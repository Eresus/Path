<?php
/**
 * Положение на сайте
 *
 * Строка с местом положения на сайте.
 *
 * @version 3.00
 *
 * @copyright 2005, ProCreat Systems, http://procreat.ru/
 * @copyright 2007, Eresus Group, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Михаил Красильников <mihalych@vsepofigu.ru>
 * @author Olex
 *
 * Данная программа является свободным программным обеспечением. Вы
 * вправе распространять ее и/или модифицировать в соответствии с
 * условиями версии 3 либо по вашему выбору с условиями более поздней
 * версии Стандартной Общественной Лицензии GNU, опубликованной Free
 * Software Foundation.
 *
 * Мы распространяем эту программу в надежде на то, что она будет вам
 * полезной, однако НЕ ПРЕДОСТАВЛЯЕМ НА НЕЕ НИКАКИХ ГАРАНТИЙ, в том
 * числе ГАРАНТИИ ТОВАРНОГО СОСТОЯНИЯ ПРИ ПРОДАЖЕ и ПРИГОДНОСТИ ДЛЯ
 * ИСПОЛЬЗОВАНИЯ В КОНКРЕТНЫХ ЦЕЛЯХ. Для получения более подробной
 * информации ознакомьтесь со Стандартной Общественной Лицензией GNU.
 *
 * Вы должны были получить копию Стандартной Общественной Лицензии
 * GNU с этой программой. Если Вы ее не получили, смотрите документ на
 * <http://www.gnu.org/licenses/>
 *
 * @package Path
 *
 * $Id$
 */

/**
 * Основной класс плагина
 *
 * @package Path
 */
class Path extends Plugin
{
	/**
	 * Версия плагина
	 *
	 * @var string
	 */
	public $version = '3.00a';

	/**
	 * Минимальная требуемая версия CMS
	 *
	 * @var string
	 */
	public $kernel = '3.00a';

	/**
	 * Название
	 *
	 * @var string
	 */
	public $title = 'Положение на сайте';

	/**
	 * Описание
	 *
	 * @var string
	 */
	public $description = 'Строка с текущим местом положения на сайте';

	/**
	 * Тип
	 *
	 * @var string
	 */
	public $type = 'client';

	/**
	 * Настройки
	 *
	 * @var string
	 */
	public $settings = array (
		'template' => '{foreach $sections s, implode=" &raquo; "}
	{if $s.isCurrent}
		{$s.caption}
	{else}
		<a href="{$s.url}" title="{$s.description}">{$s.caption}</a>
	{/if}
{/foreach}',
		'levelMin' => 0,
		'levelMax' => 0,
		'showHidden' => true,
		'showCurrent' => true,
	);

	/**
	 * Хранит элементы пути
	 *
	 * @var array
	 */
	public $path = array();

	/**
	 * Уровень вложенности текущего раздела
	 *
	 * @var int
	 */
	public $level = -1;

	/**
	 * Конструктор
	 *
	 * @return Path
	 */
	public function __construct()
	{
		parent::__construct();
		$this->listenEvents('clientOnURLSplit', 'clientOnPageRender');
	}
	//-----------------------------------------------------------------------------

	/**
	 * Возвращает разметку диалога настроек
	 *
	 * @return string  HTML
	 *
	 * @since 1.00
	 */
	public function settings()
	{
		global $page;

		$form = array(
			'name' => 'Settings',
			'caption' => $this->title.' '.$this->version,
			'width' => '700px',
			'fields' => array (
				array('type'=>'hidden','name'=>'update', 'value'=>$this->name),
				array('type'=>'edit','name'=>'levelMin','label'=>'Мин.вложенность','width'=>'20px',
					'comment'=>' 0 - любая'),
				array('type'=>'edit','name'=>'levelMax','label'=>'Макс.вложенность','width'=>'20px',
					'comment'=>' 0 - любая'),
				array('type'=>'checkbox','name'=>'showHidden','label'=>'Показывать скрытые разделы'),
				array('type'=>'checkbox','name'=>'showCurrent',
					'label'=>'Показывать текущий раздел даже если он скрытый'),
				array('type' => 'memo','name' => 'template', 'syntax' => 'html', 'height' => 10,
					'label'=>'Шаблон'),
				array('type'=>'divider'),
				array('type'=>'text',
					'value'=>"Заменяет макрос $(Path) на строку с текущим положением на сайте."),
				array('type'=>'divider'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $this->settings);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Проводит замену макроса
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	function clientOnPageRender($text)
	{
		global $Eresus;

		if (
			($this->settings['levelMin'] && ($this->level < $this->settings['levelMin']))
			||
			($this->settings['levelMax'] && ($this->level > $this->settings['levelMax']))
		)
		{
			return $text;
		}

		/*
		 * Нельзя выполнять эти действия в clientOnURLSplit, потому что в том методе ещё неизвестен
		 * текущий раздел.
		 */
		$sections = array();
		foreach ($this->path as $section)
		{
			$section['isCurrent'] = $section['id'] == $GLOBALS['page']->id;
			if (
				($section['visible'] || $this->settings['showHidden'])
				||
				($section['isCurrent'] && $this->settings['showCurrent'])
			)
			{
				$sections []= $section;
			}
		}

		$tmpl = new Path_Tempalte();
		$tmpl->loadFromString($this->settings['template']);
		$html = $tmpl->compile(array('sections' => $sections));

		$text = str_replace('$(Path)', $html, $text);

		return $text;
	}
	//-----------------------------------------------------------------------------

	/**
	 * Добавляет разделы в путь
	 *
	 * @param array $item
	 * @param string $url
	 *
	 * @return void
	 */
	public function clientOnURLSplit(array $item, $url)
	{
		$this->level++;
		$item['url'] = $GLOBALS['Eresus']->root . ('main/' == $url ? '' : $url);
		$item['parents'] = explode('/', $url);
		array_pop($item['parents']);
		$this->path[] = $item;
	}
	//-----------------------------------------------------------------------------
}

/**
 * Расширенный шаблон
 *
 * @package Path
 * @since 2.02
 */
class Path_Tempalte extends Template
{
	/**
	 * Загружает код шаблона из строки
	 *
	 * @param string $code
	 *
	 * @return void
	 *
	 * @since 1.00
	 */
	public function loadFromString($code)
	{
		$this->file = new Dwoo_Template_String($code);
	}
	//-----------------------------------------------------------------------------
}
<?php
/**
 * Положение на сайте
 *
 * Строка с местом положения на сайте.
 *
 * @version ${product.version}
 *
 * @copyright 2005, Михаил Красильников <m.krasilnikov@yandex.ru>
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author Михаил Красильников <m.krasilnikov@yandex.ru>
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
 */

/**
 * Основной класс плагина
 *
 * @package Path
 */
class Path extends Eresus_Plugin
{
    /**
     * Версия плагина
     *
     * @var string
     */
    public $version = '${product.version}';

    /**
     * Минимальная требуемая версия CMS
     *
     * @var string
     */
    public $kernel = '3.01a';

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
     * Настройки
     *
     * @var string
     */
    public $settings = array(
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
        $evd = Eresus_Kernel::app()->getEventDispatcher();
        $evd->addListener('cms.client.url_section_found', array($this, 'clientOnURLSplit'));
        $evd->addListener('cms.client.render_page', array($this, 'clientOnPageRender'));
    }

    /**
     * Возвращает разметку диалога настроек
     *
     * @return string  HTML
     *
     * @since 1.00
     */
    public function settings()
    {
        $form = array(
            'name' => 'Settings',
            'caption' => $this->title . ' ' . $this->version,
            'width' => '700px',
            'fields' => array(
                array('type' => 'hidden', 'name' => 'update', 'value' => $this->name),
                array('type' => 'edit', 'name' => 'levelMin', 'label' => 'Мин.вложенность', 'width' => '20px',
                    'comment' => ' 0 - любая'),
                array('type' => 'edit', 'name' => 'levelMax', 'label' => 'Макс.вложенность', 'width' => '20px',
                    'comment' => ' 0 - любая'),
                array('type' => 'checkbox', 'name' => 'showHidden', 'label' => 'Показывать скрытые разделы'),
                array('type' => 'checkbox', 'name' => 'showCurrent',
                    'label' => 'Показывать текущий раздел даже если он скрытый'),
                array('type' => 'memo', 'name' => 'template', 'syntax' => 'html', 'height' => 10,
                    'label' => 'Шаблон'),
                array('type' => 'divider'),
                array('type' => 'text',
                    'value' => "Заменяет макрос $(Path) на строку с текущим положением на сайте."),
                array('type' => 'divider'),
            ),
            'buttons' => array('ok', 'apply', 'cancel'),
        );
        /** @var TAdminUI $page */
        $page = Eresus_Kernel::app()->getPage();
        $result = $page->renderForm($form, $this->settings);
        return $result;
    }

    /**
     * Проводит замену макроса
     *
     * @param Eresus_Event_Render $event
     */
    public function clientOnPageRender(Eresus_Event_Render $event)
    {
        if (
            ($this->settings['levelMin'] && ($this->level < $this->settings['levelMin']))
            ||
            ($this->settings['levelMax'] && ($this->level > $this->settings['levelMax']))
        )
        {
            return;
        }

        /*
         * Нельзя выполнять эти действия в clientOnURLSplit, потому что в том методе ещё неизвестен
         * текущий раздел.
         */
        $sections = array();
        foreach ($this->path as $section)
        {
            $section['isCurrent'] = $section['id'] == Eresus_Kernel::app()->getPage()->id;
            if (
                ($section['visible'] || $this->settings['showHidden'])
                ||
                ($section['isCurrent'] && $this->settings['showCurrent'])
            )
            {
                $sections [] = $section;
            }
        }

        $tmpl = new Eresus_Template();
        $tmpl->setSource($this->settings['template']);
        $html = $tmpl->compile(array('sections' => $sections));

        $text = str_replace('$(Path)', $html, $event->getText());
        $event->setText($text);
    }

    /**
     * Добавляет разделы в путь
     *
     * @param Eresus_Event_UrlSectionFound $event
     */
    public function clientOnURLSplit(Eresus_Event_UrlSectionFound $event)
    {
        $rootUrl = Eresus_Kernel::app()->getLegacyKernel()->root;
        $this->level++;
        $item = $event->getSectionInfo();
        $item['url'] = $rootUrl . ('main/' == $event->getUrl() ? '' : $event->getUrl());
        $item['parents'] = explode('/', $event->getUrl());
        array_pop($item['parents']);
        $this->path[] = $item;
    }
}


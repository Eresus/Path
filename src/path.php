<?php
/**
 * ��������� �� �����
 *
 * ������ � ������ ��������� �� �����.
 *
 * @version 2.02
 *
 * @copyright 2005, ProCreat Systems, http://procreat.ru/
 * @copyright 2007, Eresus Group, http://eresus.ru/
 * @license http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @author ������ ������������ <mihalych@vsepofigu.ru>
 * @author Olex
 *
 * ������ ��������� �������� ��������� ����������� ������������. ��
 * ������ �������������� �� �/��� �������������� � ������������ �
 * ��������� ������ 3 ���� �� ������ ������ � ��������� ����� �������
 * ������ ����������� ������������ �������� GNU, �������������� Free
 * Software Foundation.
 *
 * �� �������������� ��� ��������� � ������� �� ��, ��� ��� ����� ���
 * ��������, ������ �� ������������� �� ��� ������� ��������, � ���
 * ����� �������� ��������� ��������� ��� ������� � ����������� ���
 * ������������� � ���������� �����. ��� ��������� ����� ���������
 * ���������� ������������ �� ����������� ������������ ��������� GNU.
 *
 * �� ������ ���� �������� ����� ����������� ������������ ��������
 * GNU � ���� ����������. ���� �� �� �� ��������, �������� �������� ��
 * <http://www.gnu.org/licenses/>
 *
 * @package Path
 *
 * $Id$
 */

/**
 * �������� ����� �������
 *
 * @package Path
 */
class Path extends Plugin
{
	/**
	 * ������ �������
	 *
	 * @var string
	 */
	public $version = '2.02a';

	/**
	 * ����������� ��������� ������ CMS
	 *
	 * @var string
	 */
	public $kernel = '2.12';

	/**
	 * ��������
	 *
	 * @var string
	 */
	public $title = '��������� �� �����';

	/**
	 * ��������
	 *
	 * @var string
	 */
	public $description = '������ � ������� ������ ��������� �� �����';

	/**
	 * ���
	 *
	 * @var string
	 */
	public $type = 'client';

	/**
	 * ���������
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
	 * ������ �������� ����
	 *
	 * @var array
	 */
	public $path = array();

	/**
	 * ������� ����������� �������� �������
	 *
	 * @var int
	 */
	public $level = -1;

	/**
	 * �����������
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
	 * ���������� �������� ������� ��������
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
				array('type'=>'edit','name'=>'levelMin','label'=>'���.�����������','width'=>'20px',
					'comment'=>' 0 - �����'),
				array('type'=>'edit','name'=>'levelMax','label'=>'����.�����������','width'=>'20px',
					'comment'=>' 0 - �����'),
				array('type'=>'checkbox','name'=>'showHidden','label'=>'���������� ������� �������'),
				array('type'=>'checkbox','name'=>'showCurrent',
					'label'=>'���������� ������� ������ ���� ���� �� �������'),
				array('type' => 'memo','name' => 'template', 'syntax' => 'html', 'height' => 10,
					'label'=>'������'),
				array('type'=>'divider'),
				array('type'=>'text',
					'value'=>"�������� ������ $(Path) �� ������ � ������� ���������� �� �����."),
				array('type'=>'divider'),
			),
			'buttons' => array('ok', 'apply', 'cancel'),
		);
		$result = $page->renderForm($form, $this->settings);
		return $result;
	}
	//-----------------------------------------------------------------------------

	/**
	 * �������� ������ �������
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
		 * ������ ��������� ��� �������� � clientOnURLSplit, ������ ��� � ��� ������ ��� ����������
		 * ������� ������.
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
	 * ��������� ������� � ����
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
 * ����������� ������
 *
 * @package Path
 * @since 2.02
 */
class Path_Tempalte extends Template
{
	/**
	 * ��������� ��� ������� �� ������
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
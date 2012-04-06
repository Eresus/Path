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
	public $kernel = '2.10';

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
		'prefix' => '',
		'delimiter' => '&nbsp;&raquo;&nbsp;',
		'link' => '<a href="$(url)" title="$(description)">$(caption)</a>',
		'current' => '$(caption)',
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
			'width' => '500px',
			'fields' => array (
				array('type'=>'hidden','name'=>'update', 'value'=>$this->name),
				array('type'=>'edit','name'=>'prefix','label'=>'������� ����','width'=>'100%'),
				array('type'=>'edit','name'=>'delimiter','label'=>'����������� ��������','width'=>'100%'),
				array('type'=>'edit','name'=>'link','label'=>'������ ������','width'=>'100%'),
				array('type'=>'edit','name'=>'current','label'=>'��� ������� ��������','width'=>'100%'),
				array('type'=>'edit','name'=>'levelMin','label'=>'���.�����������','width'=>'20px',
					'comment'=>' 0 - �����'),
				array('type'=>'edit','name'=>'levelMax','label'=>'����.�����������','width'=>'20px',
					'comment'=>' 0 - �����'),
				array('type'=>'checkbox','name'=>'showHidden','label'=>'���������� ������� �������'),
				array('type'=>'checkbox','name'=>'showCurrent',
					'label'=>'���������� ������� ������ ���� ���� �� �������'),
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
			(!$this->settings['levelMin'] || ($this->level >= $this->settings['levelMin']))
			&&
			(!$this->settings['levelMax'] || ($this->level <= $this->settings['levelMax']))
		)
		{
			$result = array();

			for ($i = 0; $i < count($this->path); $i++)
			{
				$item = $this->path[$i];
				$item['url'] = httpRoot.$item[$this->name.'_url'];

				if (
					($item['visible'] || $this->settings['showHidden'])
					||
					($this->settings['showCurrent'] && $Eresus->request['path'] == $item['url'])
				)
				{
					$template = ($i == count($this->path)-1) ?
						$this->settings['current'] :
						$this->settings['link'];
					$result[] = $this->replaceMacros($template, $item);
				}
			}
			$result = implode($this->settings['delimiter'], $result);
			$result = str_replace('$(Path)', $this->settings['prefix'].$result, $text);
		}
		else
		{
			$result = str_replace('$(Path)', '', $text);
		}
		return $result;
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

		$item[$this->name . '_url'] = 'main/' == $url ? '' : $url;
		$this->path[] = $item;
		$this->level++;
	}
	//-----------------------------------------------------------------------------
}
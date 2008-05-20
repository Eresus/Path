<?php
/**
 * "������� ������"
 *
 * Eresus 2
 *
 * ������ � ������ ��������� �� �����
 *
 * @version 2.00
 *
 * @copyright 	2005-2007, ProCreat Systems, http://procreat.ru/
 * @copyright   2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @maintainer  Mikhail Krasilnikov <mk@procreat.ru>
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
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
 */

class Path extends Plugin {
  var $version = '2.00b';
  var $kernel = '2.10rc2';
	var $title = '"������� ������"';
  var $description = '������ � ������ ��������� �� �����';
	var $type = 'client';
  var $settings = array(
    'prefix' => '',
    'delimiter' => '&nbsp;&raquo;&nbsp;',
    'link' => '<a href="$(url)" title="$(description)">$(caption)</a>',
    'current' => '$(caption)',
    'levelMin' => 0,
    'levelMax' => 0,
  );
  var $path = array(); # ������ ����
  var $level = -1; # ����������� ��������
  /**
   * �����������
   *
   * @return Path
   */
  function Path()
  {
    parent::Plugin();
    $this->listenEvents('clientOnURLSplit', 'clientOnPageRender');
  }
  //-----------------------------------------------------------------------------
  function settings()
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
        array('type'=>'edit','name'=>'levelMin','label'=>'���.�����������','width'=>'20px','comment'=>' 0 - �����'),
        array('type'=>'edit','name'=>'levelMax','label'=>'����.�����������','width'=>'20px','comment'=>' 0 - �����'),
        array('type'=>'divider'),
        array('type'=>'text','value'=>"�������� ������ $(Path) �� ������ � ������� ���������� �� �����."),
        array('type'=>'divider'),
      ),
      'buttons' => array('ok', 'apply', 'cancel'),
    );
    $result = $page->renderForm($form, $this->settings);
    return $result;
  }
  //-----------------------------------------------------------------------------
  function clientOnPageRender($text)
  {
    global $page;

    if (
      (!$this->settings['levelMin'] || ($this->level >= $this->settings['levelMin']))
      &&
      (!$this->settings['levelMax'] || ($this->level <= $this->settings['levelMax']))
    ) {
      $result = array();
      for($i = 0; $i < count($this->path); $i++) {
        $item = $this->path[$i];
        $item['url'] = httpRoot.$item[$this->name.'_url'];
        $template = ($i == count($this->path)-1)?$this->settings['current']:$this->settings['link'];
        $result[] = $this->replaceMacros($template, $item);
      }
      $result = implode($this->settings['delimiter'], $result);
      $result = str_replace('$(Path)', $this->settings['prefix'].$result, $text);
    } else $result = str_replace('$(Path)', '', $text);
    return $result;
  }
  //-----------------------------------------------------------------------------
  function clientOnURLSplit($item, $url)
  {
    $item[$this->name.'_url'] = ($url == 'main/')?'':$url;
    $this->path[] = $item;
    $this->level++;
  }
  //-----------------------------------------------------------------------------
}
?>
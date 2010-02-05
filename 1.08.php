<?php
/**
 * "������� ������" 
 *
 * Eresus 2
 * 
 * ������ � ������ ��������� �� �����
 *
 * � 2005, ProCreat Systems, http://procreat.ru/
 * � 2007, Eresus Group, http://eresus.ru/
 *
 * @version: 2.00
 * @modified: 2007-09-24
 * 
 * @author: Mikhail Krasilnikov <mk@procreat.ru>
 */

class Path extends Plugin {
  var $version = '2.00a';
  var $kernel = '2.10b2';
	var $title = '"������� ������"';
  var $description = '������ � ������ ��������� �� �����';
	var $type = 'client';
  var $settings = array(
    'prefix' => '',
    'delimiter' => '&nbsp;&raquo;&nbsp;',
    'link' => '<a href="$(link)" title="$(pageDescription)">$(pageCaption)</a>',
    'current' => '$(pageCaption)',
    'levelMin' => 0,
    'levelMax' => 0,
  );
  var $path = array(); # ������ ����
  var $level = -1; # ����������� ��������
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function Path()
  # ���������� ����������� ������������ �������
  {
  	global $plugins;

    parent::Plugin();
    $plugins->events['clientOnURLSplit'][] = $this->name;
    $plugins->events['clientOnPageRender'][] = $this->name;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
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
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
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
        $item['link'] = httpRoot.$item[$this->name.'_url'];
        $template = ($i == count($this->path)-1)?$this->settings['current']:$this->settings['link'];
        $result[] = $this->replaceMacros($template, $item);
      }
      $result = implode($this->settings['delimiter'], $result);
      $result = str_replace('$(Path)', $this->settings['prefix'].$result, $text);
    } else $result = str_replace('$(Path)', '', $text);
    return $result;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function clientOnURLSplit($item, $url)
  {
    $item[$this->name.'_url'] = ($url == 'main/')?'':$url;
    $this->path[] = $item;
    $this->level++;
  }
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
}
?>
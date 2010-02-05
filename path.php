<?php
/**
 * "Хлебные крошки" 
 *
 * Eresus 2
 * 
 * Строка с местом положения на сайте
 *
 * © 2005, ProCreat Systems, http://procreat.ru/
 * © 2007, Eresus Group, http://eresus.ru/
 *
 * @version: 2.00
 * @modified: 2007-09-24
 * 
 * @author: Mikhail Krasilnikov <mk@procreat.ru>
 */

class Path extends Plugin {
  var $version = '2.00a';
  var $kernel = '2.10b2';
	var $title = '"Хлебные крошки"';
  var $description = 'Строка с местом положения на сайте';
	var $type = 'client';
  var $settings = array(
    'prefix' => '',
    'delimiter' => '&nbsp;&raquo;&nbsp;',
    'link' => '<a href="$(link)" title="$(pageDescription)">$(pageCaption)</a>',
    'current' => '$(pageCaption)',
    'levelMin' => 0,
    'levelMax' => 0,
  );
  var $path = array(); # Строка пути
  var $level = -1; # Вложенность страницы
  #--------------------------------------------------------------------------------------------------------------------------------------------------------------#
  function Path()
  # производит регистрацию обработчиков событий
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
        array('type'=>'edit','name'=>'prefix','label'=>'Префикс пути','width'=>'100%'),
        array('type'=>'edit','name'=>'delimiter','label'=>'Разделитель разделов','width'=>'100%'),
        array('type'=>'edit','name'=>'link','label'=>'Шаблон ссылки','width'=>'100%'),
        array('type'=>'edit','name'=>'current','label'=>'Для текущей страницы','width'=>'100%'),
        array('type'=>'edit','name'=>'levelMin','label'=>'Мин.вложенность','width'=>'20px','comment'=>' 0 - любая'),
        array('type'=>'edit','name'=>'levelMax','label'=>'Макс.вложенность','width'=>'20px','comment'=>' 0 - любая'),
        array('type'=>'divider'),
        array('type'=>'text','value'=>"Заменяет макрос $(Path) на строку с текущим положением на сайте."),
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
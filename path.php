<?php
/**
 * "Хлебные крошки"
 *
 * Eresus 2
 *
 * Строка с местом положения на сайте
 *
 * @version 2.00
 *
 * @copyright 	2005-2007, ProCreat Systems, http://procreat.ru/
 * @copyright   2007-2008, Eresus Group, http://eresus.ru/
 * @license     http://www.gnu.org/licenses/gpl.txt  GPL License 3
 * @maintainer  Mikhail Krasilnikov <mk@procreat.ru>
 * @author      Mikhail Krasilnikov <mk@procreat.ru>
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
 */

class Path extends Plugin {
  var $version = '2.00b';
  var $kernel = '2.10rc2';
	var $title = '"Хлебные крошки"';
  var $description = 'Строка с местом положения на сайте';
	var $type = 'client';
  var $settings = array(
    'prefix' => '',
    'delimiter' => '&nbsp;&raquo;&nbsp;',
    'link' => '<a href="$(url)" title="$(description)">$(caption)</a>',
    'current' => '$(caption)',
    'levelMin' => 0,
    'levelMax' => 0,
  );
  var $path = array(); # Строка пути
  var $level = -1; # Вложенность страницы
  /**
   * Конструктор
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
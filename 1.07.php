<?
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
# CMS Eresus™
# © 2005, ProCreat Systems
# Web: http://procreat.ru
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#

#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
class TPath extends TPlugin {
  var $name = 'path';
  var $title = 'Path';
  var $type = 'client';
  var $version = '1.07';
  var $description = 'Строка с местом положения на сайте';
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
  function TPath()
  # производит регистрацию обработчиков событий
  {
  global $plugins;
  
    parent::TPlugin();
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
      if (count($this->path)) foreach ($this->path as $item) {
        $template = ($item['id'] == $page->id)?$this->settings['current']:$this->settings['link'];
        $template = str_replace(
          array(
            '$(link)',
            '$(pageId)',
            '$(pageName)',
            '$(pageTitle)',
            '$(pageCaption)',
            '$(pageHint)',
            '$(pageDescription)',
            '$(pageKeywords)',
            '$(pageAccessLevel)',
            '$(pageAccessName)',
          ),
          array(
            httpRoot.$item[$this->name.'_url'],
            $item['id'],
            $item['name'],
            $item['title'],
            $item['caption'],
            $item['hint'],
            $item['description'],
            $item['keywords'],
            $item['access'],
            constant('ACCESSLEVEL'.$item['access']),
          ),
          $template
        );
        $result[] = $template;
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
#-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-#
?>
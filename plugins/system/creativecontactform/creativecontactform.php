<?php
/**
 * Joomla! component creativecontactform
 *
 * @version $Id: default.php 2012-04-05 14:30:25 svn $
 * @author Creative-Solutions.net
 * @package Creative Image Slider
 * @subpackage com_creativecontactform
 * @license GNU/GPL
 *
 */

defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');
jimport('joomla.event.plugin');

class plgSystemCreativecontactform extends JPlugin {

    // 5.0 update
    private $_plugin;
    private $_params;
    private $plg_order;

    function __construct( &$subject ) {
        parent::__construct( $subject );

        // load plugin parameters and language file
        $this->_plugin = JPluginHelper::getPlugin( 'system', 'creativecontactform' );
        $this->_params = json_decode( $this->_plugin->params );
        JPlugin::loadLanguage('plg_system_creativecontactform', JPATH_ADMINISTRATOR);
    }

    function ccf_make_form($m) {
        $form_id = (int) $m[2];

        //include helper class
        require_once JPATH_SITE.'/components/com_creativecontactform/helpers/helper.php';

        $ccf_class = new CreativecontactformHelper;
        $ccf_class->form_id = $form_id;
        $ccf_class->type = 'plugin';
        $ccf_class->class_suffix = 'ccf_plg';
        $ccf_class->module_id = $this->plg_order;
        $this->plg_order ++;

        return  $ccf_class->render_html();
    }

    function render_styles_scripts() {
        $mainframe = JFactory::getApplication();

        $document = JFactory::getDocument();
        $content = $mainframe->getBody();
        $db = JFactory::getDBO();

        $version = '5.0.0-free';
        $scripts = '';

        //check if component or module loaded CCF scripts already, if no, load them
        if (strpos($content,'components/com_creativecontactform/assets/css/main.css') === false) {

            $cssFile = JURI::base(true).'/components/com_creativecontactform/assets/css/main.css?version='.$version;
            $scripts .= '<link rel="stylesheet" href="'.$cssFile.'" type="text/css" />'."\n";

            $cssFile = JURI::base(true).'/components/com_creativecontactform/assets/css/template.css?version='.$version;
            $scripts .= '<link rel="stylesheet" href="'.$cssFile.'" type="text/css" />'."\n";

            $cssFile = JURI::base(true).'/components/com_creativecontactform/assets/css/creativecss-ui.css';
            $scripts .= '<link rel="stylesheet" href="'.$cssFile.'" type="text/css" />'."\n";

            $cssFile = JURI::base(true).'/components/com_creativecontactform/assets/css/creative-scroll.css';
            $scripts .= '<link rel="stylesheet" href="'.$cssFile.'" type="text/css" />'."\n";

            $jsFile = JURI::base(true).'/components/com_creativecontactform/assets/js/creativelib.js';
            $scripts .= '<script src="'.$jsFile.'" type="text/javascript"></script>'."\n";

            $jsFile = JURI::base(true).'/components/com_creativecontactform/assets/js/creativelib-ui.js';
            $scripts .= '<script src="'.$jsFile.'" type="text/javascript"></script>'."\n";

            $jsFile = JURI::base(true).'/components/com_creativecontactform/assets/js/creative-mousewheel.js';
            $scripts .= '<script src="'.$jsFile.'" type="text/javascript"></script>'."\n";

            $jsFile = JURI::base(true).'/components/com_creativecontactform/assets/js/creative-scroll.js';
            $scripts .= '<script src="'.$jsFile.'" type="text/javascript"></script>'."\n";

            $jsFile = JURI::base(true).'/components/com_creativecontactform/assets/js/creativecontactform.js?version='.$version;
            $scripts .= '<script src="'.$jsFile.'" type="text/javascript"></script>'."\n";

        }

        $content = str_replace('</head>', $scripts . '</head>', $content);
        return $content;
    }

    function ccf_get_types_array($form_id) {
        $db = JFactory::getDBO();

        //get field types array/////////////////////////////////////////////////////////////////////////////////////////////////
        $query = "
              SELECT
              sp.id,
              st.name as type
              FROM
              `#__creative_fields` sp
              JOIN `#__creative_field_types` st ON st.id = sp.id_type
              WHERE sp.published = '1'
              AND sp.id_form = '".$form_id."'
              ORDER BY sp.ordering,sp.id
            ";
        $db->setQuery($query);
        $types_array_data = $db->loadAssocList();
        $types_array_index = 1;
        $types_array = array();
        if(is_array($types_array_data)) {
          foreach($types_array_data as $type) {
            $types_array[$types_array_index] = strtolower(str_replace(' ','-',str_replace('-','',$type['type'])));
            $types_array_index ++;
          }
        }
        return $types_array;
    }
    
    function onAfterRender() {
        $mainframe = JFactory::getApplication();
        if($mainframe->isClient('administrator'))
            return;

        $plugin = JPluginHelper::getPlugin('system', 'creativecontactform');
        $pluginParams = json_decode( $plugin->params );

        $content = $mainframe->getBody();

        //If shortcode found, then add scripts
        if(preg_match('/(\[creativecontactform id="([0-9]+)"\])/s',$content))
            $content = $this->render_styles_scripts();

        // if there is no shortcode, and module or component does not load CCF as well, then return
        if(!preg_match('/(\[creativecontactform id="([0-9]+)"\])/s',$content))
            return;
      
        //if shortcode found, render form
        if(preg_match('/(\[creativecontactform id="([0-9]+)"\])/s',$content)) {
            $this->plg_order = 10000;
            //plugin 
            $content = preg_replace_callback('/(\[creativecontactform id="([0-9]+)"\])/s',array($this, 'ccf_make_form'),$content);
        }
      
        $mainframe->setBody($content);
    }

}
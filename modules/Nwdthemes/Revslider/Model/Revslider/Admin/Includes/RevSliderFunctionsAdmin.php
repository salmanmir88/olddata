<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2019 ThemePunch
 */

namespace Nwdthemes\Revslider\Model\Revslider\Admin\Includes;

use \Nwdthemes\Revslider\Helper\Framework;
use \Nwdthemes\Revslider\Helper\Query;
use \Nwdthemes\Revslider\Model\Revslider\Admin\Includes\RevSliderPluginUpdate;
use \Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderFunctions;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderObjectLibrary;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderSlide;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderSlider;

class RevSliderFunctionsAdmin extends RevSliderFunctions {

	/**
	 * get the full object of:
	 * +- Slider Templates
	 * +- Created Slider
	 * +- Object Library Images
	 * - Object Library Videos
	 * +- SVG
	 * +- Font Icons
	 * - layers
	 **/
	public function get_full_library($include = array('all'), $tmp_slide_uid = array(), $refresh_from_server = false, $get_static_slide = false){
		$include	= (array)$include;
		$template	= new RevSliderTemplate(self::$frameworkHelper);
		$library	= new RevSliderObjectLibrary(self::$frameworkHelper);
		$slide		= new RevSliderSlide(self::$frameworkHelper);
		$object		= array();
		$tmp_slide_uid = ($tmp_slide_uid !== false) ? (array)$tmp_slide_uid : array();

		if($refresh_from_server){
			if(in_array('all', $include) || in_array('moduletemplates', $include)){ //refresh template list from server
				$template->_get_template_list(true);
				if(!isset($object['moduletemplates'])) $object['moduletemplates'] = array();
				$object['moduletemplates']['tags'] = $template->get_template_categories();
				asort($object['moduletemplates']['tags']);
			}
			if(in_array('all', $include) || in_array('layers', $include) || in_array('videos', $include) || in_array('images', $include) || in_array('objects', $include)){ //refresh object list from server
				$library->_get_list(true);
			}
			if(in_array('all', $include) || in_array('layers', $include)){ //refresh object list from server
				if(!isset($object['layers'])) $object['layers'] = array();
				$object['layers']['tags'] = $library->get_objects_categories('4');
				asort($object['layers']['tags']);
			}
			if(in_array('all', $include) || in_array('videos', $include)){ //refresh object list from server
				if(!isset($object['videos'])) $object['videos'] = array();
				$object['videos']['tags'] = $library->get_objects_categories('3');
				asort($object['videos']['tags']);
			}
			if(in_array('all', $include) || in_array('images', $include)){ //refresh object list from server
				if(!isset($object['images'])) $object['images'] = array();
				$object['images']['tags'] = $library->get_objects_categories('2');
				asort($object['images']['tags']);
			}
			if(in_array('all', $include) || in_array('objects', $include)){ //refresh object list from server
				if(!isset($object['objects'])) $object['objects'] = array();
				$object['objects']['tags'] = $library->get_objects_categories('1');
				asort($object['objects']['tags']);
			}
		}

		if(in_array('moduletemplates', $include) || in_array('all', $include)){
			if(!isset($object['moduletemplates'])) $object['moduletemplates'] = array();
			$object['moduletemplates']['items']	= $template->get_tp_template_sliders_for_library($refresh_from_server);
		}
		if(in_array('moduletemplateslides', $include) || in_array('all', $include)){
			if(!isset($object['moduletemplateslides'])) $object['moduletemplateslides'] = array();
			$object['moduletemplateslides']['items'] = $template->get_tp_template_slides_for_library($tmp_slide_uid);
		}
		if(in_array('modules', $include) || in_array('all', $include)){
			if(!isset($object['modules'])) $object['modules'] = array();
			$object['modules']['items'] = $this->get_slider_overview();
		}
		if(in_array('moduleslides', $include) || in_array('all', $include)){
			if(!isset($object['moduleslides'])) $object['moduleslides'] = array();
			$object['moduleslides']['items'] = $slide->get_slides_for_library($tmp_slide_uid, $get_static_slide);
		}
		if(in_array('svgs', $include) || in_array('all', $include)){
			if(!isset($object['svgs'])) $object['svgs'] = array();
			$object['svgs']['items'] = $library->get_svg_sets_full();
		}
		if(in_array('fonticons', $include) || in_array('all', $include)){
			if(!isset($object['fonticons'])) $object['fonticons'] = array();
			$object['fonticons']['items'] = $library->get_font_icons();
		}
		if(in_array('layers', $include) || in_array('all', $include)){
			if(!isset($object['layers'])) $object['layers'] = array();
			$object['layers']['items'] = $library->load_objects('4');
		}
		if(in_array('videos', $include) || in_array('all', $include)){
			if(!isset($object['videos'])) $object['videos'] = array();
			$object['videos']['items'] = $library->load_objects('3');
		}
		if(in_array('images', $include) || in_array('all', $include)){
			if(!isset($object['images'])) $object['images'] = array();
			$object['images']['items'] = $library->load_objects('2');
		}
		if(in_array('objects', $include) || in_array('all', $include)){
			if(!isset($object['objects'])) $object['objects'] = array();
			$object['objects']['items'] = $library->load_objects('1');
		}
		/*if(in_array('wpimages', $include) || in_array('all', $include)){
			$data = $this->get_request_var('data');
			$after = $this->get_val($data, 'after', false);
			if(!isset($object['wpimages'])) $object['wpimages'] = array();
			$object['wpimages']['items'] = $library->load_wp_objects('image', $after);
		}
		if(in_array('wpvideos', $include) || in_array('all', $include)){
			$data = $this->get_request_var('data');
			$after = $this->get_val($data, 'after', false);
			if(!isset($object['wpvideos'])) $object['wpvideos'] = array();
			$object['wpvideos']['items'] = $library->load_wp_objects('video', $after);
		}*/

		return $object;
	}


	/**
	 * get the short library with categories and how many elements exist
	 **/
	public function get_short_library(){

		$template = new RevSliderTemplate(self::$frameworkHelper);
		$library = new RevSliderObjectLibrary(self::$frameworkHelper);
		$sliders = $this->get_slider_overview();

		$slider_cat = array();
		if(!empty($sliders)){
			foreach($sliders as $slider){
				$tags = $this->get_val($slider, 'tags', array());
				if(!empty($tags)){
					foreach($tags as $tag){
						if(trim($tag) !== '' && !isset($slider_cat[$tag])) $slider_cat[$tag] = ucwords($tag);
					}
				}
			}
		}

		$svg_cat = $library->get_svg_categories();
		$oc	= $library->get_objects_categories('1');
		$oc2 = $library->get_objects_categories('2');
		$oc3 = $library->get_objects_categories('3');
		$oc4 = $library->get_objects_categories('4');
		$t_cat = $template->get_template_categories();
		$font_cat = $library->get_font_tags();

		$wpi = array('jpg' => 'jpg', 'png' => 'png');
		$wpv = array('mpeg' => 'mpeg', 'mp4' => 'mp4', 'ogv' => 'ogv');

		asort($wpi);
		asort($wpv);
		asort($oc);
		asort($t_cat);
		asort($slider_cat);
		asort($svg_cat);
		asort($font_cat);

		return array(
			'moduletemplates' => array('tags' => $t_cat),
			'modules'	=> array('tags' => $slider_cat),
			'svgs'		=> array('tags' => $svg_cat),
			'fonticons'	=> array('tags' => $font_cat),
			'layers'	=> array('tags' => $oc4),
			'videos'	=> array('tags' => $oc3),
			'images'	=> array('tags' => $oc2),
			'objects'	=> array('tags' => $oc)/*,
			'wpimages'	=> array('tags' => $wpi),
			'wpvideos'	=> array('tags' => $wpv)*/
		);
	}


	/**
	 * Get Sliders data for the overview page
	 **/
	public function get_slider_overview(){
		$rs_slider	= new RevSliderSlider(self::$frameworkHelper);
		$sliders	= $rs_slider->get_sliders(false);

		$rs_folder	= new RevSliderFolder(self::$frameworkHelper);
		$folders	= $rs_folder->get_folders();

		$sliders 	= array_merge($sliders, $folders);
		$data		= array();

		if(!empty($sliders)){
			foreach($sliders as $slider){
				$slider->init_layer = false;
				$data[] = $slider->get_overview_data();
			}
		}

		return $data;
	}


	/**
	 * insert custom animations
	 * @before: RevSliderOperations::insertCustomAnim();
	 */
	public function insert_animation($animation, $type){
		$handle = $this->get_val($animation, 'name', false);
		$result = false;

		if($handle !== false && trim($handle) !== ''){
			$wpdb = self::$frameworkHelper->getQueryHelper();

			//check if handle exists
			$arr = array(
				'handle'	=> $this->get_val($animation, 'name'),
				'params'	=> json_encode($animation),
				'settings'	=> $type
			);

			$result = $wpdb->insert($wpdb->prefix . RevSliderFront::TABLE_LAYER_ANIMATIONS, $arr);
		}

		return ($result) ? $wpdb->insert_id : $result;
	}


	/**
	 * update custom animations
	 * @before: RevSliderOperations::updateCustomAnim();
	 */
	public function update_animation($animation_id, $animation, $type){
		$wpdb = self::$frameworkHelper->getQueryHelper();

		$arr = array(
			'handle'	=> $this->get_val($animation, 'name'),
			'params'	=> json_encode($animation),
			'settings'	=> $type
		);

		$result = $wpdb->update($wpdb->prefix . RevSliderFront::TABLE_LAYER_ANIMATIONS, $arr, array('id' => $animation_id));

		return ($result) ? $animation_id : $result;
	}


	/**
	 * delete custom animations
	 * @before: RevSliderOperations::deleteCustomAnim();
	 */
	public function delete_animation($animation_id){
		$wpdb = self::$frameworkHelper->getQueryHelper();

		$result = $wpdb->delete($wpdb->prefix . RevSliderFront::TABLE_LAYER_ANIMATIONS, array('id' => $animation_id));

		return $result;
	}


	/**
	 * @since: 5.3.0
	 * create a page with revslider shortcodes included
	 * @before: RevSliderOperations::create_slider_page();
	 **/
	public static function create_slider_page($added, $modals = array(), $additions = array()){
		$new_page_id = 0;

		if(!is_array($added)) return self::$frameworkHelper->apply_filters('revslider_create_slider_page', $new_page_id, $added);

		$content = '';
		$page_id = self::$frameworkHelper->get_option('rs_import_page_id', 1);

		//get alias of all new Sliders that got created and add them as a shortcode onto a page
		foreach($added as $sid){
			$slider = new RevSliderSlider(self::$frameworkHelper);
			$slider->init_by_id($sid);
			$alias = $slider->get_alias();
			if($alias !== ''){
				$usage		= (in_array($sid, $modals, true)) ? ' usage="modal"' : '';
				$addition	= (isset($additions[$sid])) ? ' ' . $additions[$sid] : '';
				if(strpos($addition, 'usage=\"modal\"') !== false) $usage = ''; //remove as not needed two times
				$content .= '{{block class="Nwdthemes\Revslider\Block\Revslider" alias="'.$alias.'"'.$usage.$addition.'}}' . "\n"; //this way we will reorder as last comes first
			}
		}

		if($content !== ''){

            $_title = __('Revolution Slider Page') . ' - ' . self::$frameworkHelper->sanitize_title($slider->getTitle());
            $_id = 'revslider-' . $page_id . '-' . time() . '-' . self::$frameworkHelper->sanitize_title_with_dashes($alias);

            $newPageId = self::$frameworkHelper->createPage($_id, $_title, $content);

            if ($newPageId) {
                $page_id++;
                self::$frameworkHelper->update_option('rs_import_page_id', $page_id);
            }
		}

		return self::$frameworkHelper->apply_filters('revslider_create_slider_page', $newPageId, $added);
	}

	/**
	 * add notices from ThemePunch
	 * @since: 4.6.8
	 */
	public function add_notices(){
		$_n = array();
		$notices = (array)self::$frameworkHelper->get_option('revslider-notices', false);

		if(!empty($notices) && is_array($notices)){
			$n_discarted = self::$frameworkHelper->get_option('revslider-notices-dc', array());

			foreach($notices as $notice) if ($notice) {
				//check if global or just on plugin related pages
				if($notice->version === true || !in_array($notice->code, $n_discarted) && version_compare($notice->version, Framework::RS_REVISION, '>=')){
					$_n[] = $notice;
				}
			}
		}

		//push whatever notices we might need
		return $_n;
	}

	/**
	 * get basic v5 Slider data
	 **/
	public function get_v5_slider_data(){
		$wpdb = self::$frameworkHelper->getQueryHelper();

		$sliders	= array();
		$do_order	= 'id';
		$direction	= 'ASC';

		$slider_data = $wpdb->get_results($wpdb->prepare("SELECT `id`, `title`, `alias`, `type` FROM ".$wpdb->prefix . RevSliderFront::TABLE_SLIDER."_bkp ORDER BY %s %s", array($do_order, $direction)), Query::ARRAY_A);

		if(!empty($slider_data)){
			foreach($slider_data as $data){
				if($this->get_val($data, 'type') == 'template') continue;

				$sliders[] = $data;
			}
		}

		return $sliders;
	}

	/**
	 * get basic v5 Slider data
	 **/
	public function reimport_v5_slider($id){
		$wpdb = self::$frameworkHelper->getQueryHelper();

		$done = false;

		$slider_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . RevSliderFront::TABLE_SLIDER."_bkp WHERE `id` = %s", $id), Query::ARRAY_A);

		if(!empty($slider_data)){
			$slides_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . RevSliderFront::TABLE_SLIDES."_bkp WHERE `slider_id` = %s", $id), Query::ARRAY_A);
			$static_slide_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . RevSliderFront::TABLE_STATIC_SLIDES."_bkp WHERE `slider_id` = %s", $id), Query::ARRAY_A);

			if(!empty($slides_data)){
				//check if the ID's exist in the new tables, if yes overwrite, if not create
				$slider_v6 = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . RevSliderFront::TABLE_SLIDER." WHERE `id` = %s", $id), Query::ARRAY_A);
				unset($slider_data['id']);
				if(!empty($slider_v6)){
					/**
					 * push the old data to the already imported Slider
					 **/
					$result = $wpdb->update($wpdb->prefix . RevSliderFront::TABLE_SLIDER, $slider_data, array('id' => $id));
				}else{
					$result	= $wpdb->insert($wpdb->prefix . RevSliderFront::TABLE_SLIDER, $slider_data);
					$id		= ($result) ? $wpdb->insert_id : false;
				}
				if($id !== false){
					foreach($slides_data as $k => $slide_data){
						$slide_data['slider_id'] = $id;
						$slide_v6 = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . RevSliderFront::TABLE_SLIDES." WHERE `id` = %s", $slide_data['id']), Query::ARRAY_A);
						$slide_id = $slide_data['id'];
						unset($slide_data['id']);
						if(!empty($slide_v6)){
							$result = $wpdb->update($wpdb->prefix . RevSliderFront::TABLE_SLIDES, $slide_data, array('id' => $slide_id));
						}else{
							$result	= $wpdb->insert($wpdb->prefix . RevSliderFront::TABLE_SLIDES, $slide_data);
						}
					}
					if(!empty($static_slide_data)){
						$static_slide_data['slider_id'] = $id;
						$slide_v6 = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . RevSliderFront::TABLE_STATIC_SLIDES." WHERE `id` = %s", $static_slide_data['id']), Query::ARRAY_A);
						$slide_id = $static_slide_data['id'];
						unset($static_slide_data['id']);
						if(!empty($slide_v6)){
							$result = $wpdb->update($wpdb->prefix . RevSliderFront::TABLE_STATIC_SLIDES, $static_slide_data, array('id' => $slide_id));
						}else{
							$result	= $wpdb->insert($wpdb->prefix . RevSliderFront::TABLE_STATIC_SLIDES, $static_slide_data);
						}
					}

					$slider = new RevSliderSlider(self::$frameworkHelper);
					$slider->init_by_id($id);

					$upd = new RevSliderPluginUpdate($this->_frameworkHelper);

					$upd->upgrade_slider_to_latest($slider);
					$done = true;
				}
			}
		}

		return $done;
	}

	/**
	 * returns an object of current system values
	 **/
	public function get_system_requirements(){
		$dir	= $this->_frameworkHelper->wp_upload_dir();
		$basedir = $this->get_val($dir, 'basedir').'/';
		$ml		= ini_get('memory_limit');
		$mlb	= $this->_frameworkHelper->wp_convert_hr_to_bytes($ml);
		$umf	= ini_get('upload_max_filesize');
		$umfb	= $this->_frameworkHelper->wp_convert_hr_to_bytes($umf);
		$pms	= ini_get('post_max_size');
		$pmsb	= $this->_frameworkHelper->wp_convert_hr_to_bytes($pms);


		$mlg  = ($mlb >= 268435456) ? true : false;
		$umfg = ($umfb >= 33554432) ? true : false;
		$pmsg = ($pmsb >= 33554432) ? true : false;

		return array(
			'memory_limit' => array(
				'has' => $this->_frameworkHelper->size_format($mlb),
				'min' => $this->_frameworkHelper->size_format(268435456),
				'good'=> $mlg
			),
			'upload_max_filesize' => array(
				'has' => $this->_frameworkHelper->size_format($umfb),
				'min' => $this->_frameworkHelper->size_format(33554432),
				'good'=> $umfg
			),
			'post_max_size' => array(
				'has' => $this->_frameworkHelper->size_format($pmsb),
				'min' => $this->_frameworkHelper->size_format(33554432),
				'good'=> $pmsg
			),
			'upload_folder_writable'	=> $this->_frameworkHelper->wp_is_writable($basedir),
			'object_library_writable'	=> $this->_frameworkHelper->wp_image_editor_supports(array('methods' => array('resize', 'save'))),
			'server_connect'			=> $this->_frameworkHelper->get_option('revslider-connection', false),
		);
	}

	/**
	 * import a media file uploaded through the browser to the media library
	 **/
	public function import_upload_media(){

		$wp_filesystem = $this->_frameworkHelper->getFilesystemHelper();

		$import_file = $this->get_val($_FILES, 'import_file');
		$error		 = $this->get_val($import_file, 'error');
		$return		 = array('error' => __('File not found', 'revslider'));

		switch($error){
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				return array('error' => __('No file sent', 'revslider'));
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return array('error' => __('Exceeded filesize limit', 'revslider'));
			default:
			break;
		}

		$path = $this->get_val($import_file, 'tmp_name');
		if(isset($path['error'])) return array('error' => $path['error']);

		if(file_exists($path) == false) return array('error' => __('File not found', 'revslider'));

		$file_mime = mime_content_type($path);
		$allow = array(
			'jpg|jpeg|jpe'	=> 'image/jpeg',
			'gif'			=> 'image/gif',
			'png'			=> 'image/png',
			'bmp'			=> 'image/bmp',
			'mpeg|mpg|mpe'	=> 'video/mpeg',
			'mp4|m4v'		=> 'video/mp4',
			'ogv'			=> 'video/ogg',
			'webm'			=> 'video/webm'
		);

		if(!in_array($file_mime, $allow)) return array('error' => __('Slider Revolution doesn\'t allow this filetype', 'revslider'));

		$file_name = $this->get_val($import_file, 'name');
		$new_path = $this->_frameworkHelper->getImagesHelper()->imageBaseDir() . DIRECTORY_SEPARATOR . $file_name;
		$i = 0;
		while(file_exists($new_path)){
			$i++;
			$new_path = $this->_frameworkHelper->getImagesHelper()->imageBaseDir() . DIRECTORY_SEPARATOR . $i . '-' . $file_name;
		}

		if(move_uploaded_file($path, $new_path)){

			$imageId = $this->_frameworkHelper->getImagesHelper()->get_image_id_by_url($new_path);
			if ($imageData = $this->_frameworkHelper->wp_get_attachment_image_src($imageId, 'full')) {
				$url = $this->get_val($imageData, 0, '');
				$width	= $this->get_val($imageData, 1, '');
				$height	= $this->get_val($imageData, 2, '');
				$return = array(
					'error' => false,
					'id' => $imageId,
					'path' => $url,
					'width' => $width,
					'height' => $height
				);
			}

		}

		return $return;
	}

	public function sort_by_slide_order($a, $b) {
		return $a['slide_order'] - $b['slide_order'];
	}


	/**
	 * Create Multilanguage for JavaScript
	 */
	public function get_javascript_multilanguage(){
		$lang = array(
			'previewnotworking' => __('The preview could not be loaded due to some conflict with another Magento theme or plugin', 'revslider'),
			'checksystemnotworking' => __('Server connection issues, contact your hosting provider for further assistance', 'revslider'),
			'editskins' => __('Edit Skin List', 'revslider'),
			'globalcoloractive' => __('Color Skin Active', 'revslider'),
			'corejs' => __('Core JavaScript', 'revslider'),
			'corecss' => __('Core CSS', 'revslider'),
			'coretools' => __('Core Tools (GreenSock & Co)', 'revslider'),
			'enablecompression' => __('Enable Server Compression', 'revslider'),
			'noservercompression' => __('Not Available, read FAQ', 'revslider'),
			'servercompression' => __('Serverside Compression', 'revslider'),
			'sizeafteroptim' => __('Size after Optimization', 'revslider'),
			'chgimgsizesrc' => __('Change Image Size or Src', 'revslider'),
			'pickandim' => __('Pick another Dimension', 'revslider'),
			'optimize' => __('Optimize', 'revslider'),
			'savechanges' => __('Save Changes', 'revslider'),
			'applychanges' => __('Apply Changes', 'revslider'),
			'suggestion' => __('Suggestion', 'revslider'),
			'toosmall' => __('Too Small', 'revslider'),
			'standard1x' => __('Standard (1x)', 'revslider'),
			'retina2x' => __('Retina (2x)', 'revslider'),
			'oversized' => __('Oversized', 'revslider'),
			'quality' => __('Quality', 'revslider'),
			'file' => __('File', 'revslider'),
			'resize' => __('Resize', 'revslider'),
			'lowquality' => __('Optimized (Low Quality)', 'revslider'),
			'notretinaready' => __('Not Retina Ready', 'revslider'),
			'element' => __('Element', 'revslider'),
			'calculating' => __('Calculating...', 'revslider'),
			'filesize' => __('File Size', 'revslider'),
			'dimension' => __('Dimension', 'revslider'),
			'dimensions' => __('Dimensions', 'revslider'),
			'optimization' => __('Optimization', 'revslider'),
			'optimized' => __('Optimized', 'revslider'),
			'smartresize' => __('Smart Resize', 'revslider'),
			'optimal' => __('Optimal', 'revslider'),
			'recommended' => __('Recommended', 'revslider'),
			'hrecommended' => __('Highly Recommended', 'revslider'),
			'optimizertitel' => __('File Size Optimizer', 'revslider'),
			'loadedmediafiles' => __('Loaded Media Files', 'revslider'),
			'loadedmediainfo' => __('Optimize to save up to ', 'revslider'),
			'optselection' => __('Optimize Selection', 'revslider'),
			'visibility' => __('Visibility', 'revslider'),
			'layers' => __('Layers', 'revslider'),
			'videoid' => __('Video ID', 'revslider'),
			'youtubeid' => __('YouTube ID', 'revslider'),
			'vimeoid' => __('Vimeo ID', 'revslider'),
			'poster' => __('Poster', 'revslider'),
			'youtubeposter' => __('YouTube Poster', 'revslider'),
			'vimeoposter' => __('Vimeo Poster', 'revslider'),
			'postersource' => __('Poster Image', 'revslider'),
			'medialibrary' => __('Media Library', 'revslider'),
			'objectlibrary' => __('Object Library', 'revslider'),
			'videosource' => __('Video Source', 'revslider'),
			'imagesource' => __('Image Source', 'revslider'),
			'extimagesource' => __('External Image Source', 'revslider'),
			'mediasrcimage' => __('Image Based', 'revslider'),
			'mediasrcext' => __('External Image', 'revslider'),
			'mediasrcsolid' => __('Background Color', 'revslider'),
			'mediasrctrans' => __('Transparent', 'revslider'),
			'please_wait_a_moment' => __('Please Wait a Moment', 'revslider'),
			'backgrounds' => __('Backgrounds', 'revslider'),
			'name' => __('Name', 'revslider'),
			'colorpicker' => __('Color Picker', 'revslider'),
			'savecontent' => __('Save Content', 'revslider'),
			'modulbackground' => __('Module Background', 'revslider'),
			'wrappingtag' => __('Wrapping Tag', 'revslider'),
			'tag' => __('Tag', 'revslider'),
			'content' => __('Content', 'revslider'),
			'nolayerstoedit' => __('No Layers to Edit', 'revslider'),
			'layermedia' => __('Layer Media', 'revslider'),
			'oppps' => __('Ooppps....', 'revslider'),
			'no_nav_changes_done' => __('None of the Settings changed. There is Nothing to Save', 'revslider'),
			'no_preset_name' => __('Enter Preset Name to Save or Delete', 'revslider'),
			'customlayergrid_size_title' => __('Custom Size is currently Disabled', 'revslider'),
			'customlayergrid_size_content' => __('The Current Size is set to calculate the Layer grid sizes Automatically.<br>Do you want to continue with Custom Sizes or do you want to keep the Automatically generated sizes ?', 'revslider'),
			'customlayergrid_answer_a' => __('Keep Auto Sizes', 'revslider'),
			'customlayergrid_answer_b' => __('Use Custom Sizes', 'revslider'),
			'removinglayer_title' => __('What should happen Next?', 'revslider'),
			'removinglayer_attention' => __('Need Attention by removing', 'revslider'),
			'removinglayer_content' => __('Where do you want to move the Inherited Layers?', 'revslider'),
			'dragAndDropFile' => __('Drag & Drop Import File', 'revslider'),
			'or' => __('or', 'revslider'),
			'clickToChoose' => __('Click to Choose', 'revslider'),
			'embed' => __('Embed', 'revslider'),
			'export' => __('Export', 'revslider'),
			'delete' => __('Delete', 'revslider'),
			'duplicate' => __('Duplicate', 'revslider'),
			'preview' => __('Preview', 'revslider'),
			'tags' => __('Tags', 'revslider'),
			'folders' => __('Folder', 'revslider'),
			'rename' => __('Rename', 'revslider'),
			'root' => __('Root Level', 'revslider'),
			'simproot' => __('Root', 'revslider'),
			'show' => __('Show', 'revslider'),
			'perpage' => __('Per Page', 'revslider'),
			'convertedlayer' => __('Layer converted Successfully', 'revslider'),
			'layerloopdisabledduetimeline' => __('Layer Loop Effect disabled', 'revslider'),
			'layerbleedsout' => __('<b>Layer width bleeds out of Grid:</b><br>-Auto Layer width has been removed<br>-Line Break set to Content Based', 'revslider'),
			'noMultipleSelectionOfLayers' => __('Multiple Layerselection not Supported<br>in Animation Mode', 'revslider'),
			'closeNews' => __('Close News', 'revslider'),
			'copyrightandlicenseinfo' => __('&copy; Copyright & License Info', 'revslider'),
			'registered' => __('Registered', 'revslider'),
			'notRegisteredNow' => __('Unregistered', 'revslider'),
			'dismissmessages' => __('Dismiss Messages', 'revslider'),
			'someAddonnewVersionAvailable' => __('Some AddOns have new versions available', 'revslider'),
			'newVersionAvailable' => __('New Version Available. Please Update', 'revslider'),
			'addonsmustbeupdated' => __('AddOns Outdated. Please Update', 'revslider'),
			'notRegistered' => __('Plugin is not Registered', 'revslider'),
			'notRegNoPremium' => __('Register to unlock Premium Features', 'revslider'),
			'notRegNoAll' => __('Register to Unlock all Features', 'revslider'),
			'notRegNoAddOns' => __('Register to unlock AddOns', 'revslider'),
			'notRegNoSupport' => __('Register to unlock Support', 'revslider'),
			'notRegNoLibrary' => __('Register to unlock Library', 'revslider'),
			'notRegNoUpdates' => __('Register to unlock Updates', 'revslider'),
			'notRegNoTemplates' => __('Register to unlock Templates', 'revslider'),
			'areyousureupdateplugin' => __('Do you want to start the Update process?', 'revslider'),
			'updatenow' => __('Update Now', 'revslider'),
			'toplevels' => __('Higher Level', 'revslider'),
			'siblings' => __('Current Level', 'revslider'),
			'otherfolders' => __('Other Folders', 'revslider'),
			'parent' => __('Parent Level', 'revslider'),
			'from' => __('from', 'revslider'),
			'to' => __('to', 'revslider'),
			'actionneeded' => __('Action Needed', 'revslider'),
			'updatedoneexist' => __('Done', 'revslider'),
			'updateallnow' => __('Update All', 'revslider'),
			'updatelater' => __('Update Later', 'revslider'),
			'addonsupdatemain' => __('The following AddOns require an update:', 'revslider'),
			'addonsupdatetitle' => __('AddOns need attention', 'revslider'),
			'updatepluginfailed' => __('Updating Plugin Failed', 'revslider'),
			'updatingplugin' => __('Updating Plugin...', 'revslider'),
			'licenseissue' => __('License validation issue Occured. Please contact our Support.', 'revslider'),
			'leave' => __('Back to Overview', 'revslider'),
			'reLoading' => __('Page is reloading...', 'revslider'),
			'updateplugin' => __('Update Plugin', 'revslider'),
			'updatepluginsuccess' => __('Slider Revolution Plugin updated Successfully.', 'revslider'),
			'updatepluginfailure' => __('Slider Revolution Plugin updated Failure:', 'revslider'),
			'updatepluginsuccesssubtext' => __('Slider Revolution Plugin updated Successfully to', 'revslider'),
			'reloadpage' => __('Reload Page', 'revslider'),
			'loading' => __('Loading', 'revslider'),
			'globalcolors' => __('Global Colors', 'revslider'),
			'elements' => __('Elements', 'revslider'),
			'loadingthumbs' => __('Loading Thumbnails...', 'revslider'),
			'jquerytriggered' => __('jQuery Triggered', 'revslider'),
			'atriggered' => __('&lt;a&gt; Tag Link', 'revslider'),
			'firstslide' => __('First Slide', 'revslider'),
			'lastslide' => __('Last Slide', 'revslider'),
			'nextslide' => __('Next Slide', 'revslider'),
			'previousslide' => __('Previous Slide', 'revslider'),
			'somesourceisnotcorrect' => __('Some Settings in Slider <strong>Source may not complete</strong>.<br>Please Complete All Settings in Slider Sources.', 'revslider'),
			'somelayerslocked' => __('Some Layers are <strong>Locked</strong> and/or <strong>Invisible</strong>.<br>Change Status in Timeline.', 'revslider'),
			'editorisLoading' => __('Editor is Loading...', 'revslider'),
			'addingnewblankmodule' => __('Adding new Blank Module...', 'revslider'),
			'opening' => __('Opening', 'revslider'),
			'featuredimages' => __('Featured Images', 'revslider'),
			'images' => __('Images', 'revslider'),
			'none' => __('None', 'revslider'),
			'select' => __('Select', 'revslider'),
			'reset' => __('Reset', 'revslider'),
			'custom' => __('Custom', 'revslider'),
			'out' => __('OUT', 'revslider'),
			'in' => __('IN', 'revslider'),
			'sticky_navigation' => __('Navigation Options', 'revslider'),
			'sticky_slider' => __('Module General Options', 'revslider'),
			'sticky_slide' => __('Slide Options', 'revslider'),
			'sticky_layer' => __('Layer Options', 'revslider'),
			'imageCouldNotBeLoaded' => __('Set a Slide Background Image to use this feature', 'revslider'),
			'oppps' => __('Ooppps....', 'revslider'),
			'no_nav_changes_done' => __('None of the Settings changed. There is Nothing to Save', 'revslider'),
			'no_preset_name' => __('Enter Preset Name to Save or Delete', 'revslider'),
			'customlayergrid_size_title' => __('Custom Size is currently Disabled', 'revslider'),
			'customlayergrid_size_content' => __('The Current Size is set to calculate the Layer grid sizes Automatically.<br>Do you want to continue with Custom Sizes or do you want to keep the Automatically generated sizes ?', 'revslider'),
			'customlayergrid_answer_a' => __('Keep Auto Sizes', 'revslider'),
			'customlayergrid_answer_b' => __('Use Custom Sizes', 'revslider'),
			'removinglayer_title' => __('What should happen Next?', 'revslider'),
			'removinglayer_attention' => __('Need Attention by removing', 'revslider'),
			'removinglayer_content' => __('Where do you want to move the Inherited Layers?', 'revslider'),
			'dragAndDropFile' => __('Drag & Drop Import File', 'revslider'),
			'or' => __('or', 'revslider'),
			'clickToChoose' => __('Click to Choose', 'revslider'),
			'embed' => __('Embed', 'revslider'),
			'export' => __('Export', 'revslider'),
			'exporthtml' => __('HTML', 'revslider'),
			'delete' => __('Delete', 'revslider'),
			'duplicate' => __('Duplicate', 'revslider'),
			'preview' => __('Preview', 'revslider'),
			'tags' => __('Tags', 'revslider'),
			'folders' => __('Folder', 'revslider'),
			'rename' => __('Rename', 'revslider'),
			'root' => __('Root Level', 'revslider'),
			'simproot' => __('Root', 'revslider'),
			'show' => __('Show', 'revslider'),
			'perpage' => __('Per Page', 'revslider'),
			'releaseToAddLayer' => __('Release to Add Layer', 'revslider'),
			'releaseToUpload' => __('Release to Upload file', 'revslider'),
			'moduleZipFile' => __('Module .zip', 'revslider'),
			'importing' => __('Processing Import of', 'revslider'),
			'importfailure' => __('An Error Occured while importing', 'revslider'),
			'successImportFile' => __('File Succesfully Imported', 'revslider'),
			'importReport' => __('Import Report', 'revslider'),
			'updateNow' => __('Update Now', 'revslider'),
			'activateToUpdate' => __('Activate To Update', 'revslider'),
			'activated' => __('Activated', 'revslider'),
			'notActivated' => __('Not Activated', 'revslider'),
			'embedingLine1' => __('Standard Module Embedding', 'revslider'),
			'embedingLine2' => __('For the <b>pages and posts</b> editor insert the Shortcode:', 'revslider'),
			'embedingLine2a' => __('To Use it as <b>Modal</b> on <b>pages and posts</b> editor insert the Shortcode:', 'revslider'),
			'embedingLine3' => __('From the <b>widgets panel</b> drag the "Revolution Module" widget to the desired sidebar.', 'revslider'),
			'embedingLine4' => __('Advanced Module Embedding', 'revslider'),
			'embedingLine5' => __('For the <b>theme html</b> use:', 'revslider'),
			'embedingLine6' => __('To add the slider only to the homepage, use:', 'revslider'),
			'embedingLine7' => __('To add the slider only to single Pages, use:', 'revslider'),
			'noLayersSelected' => __('Select a Layer', 'revslider'),
			'layeraction_group_link' => __('Link Actions', 'revslider'),
			'layeraction_group_slide' => __('Slide Actions', 'revslider'),
			'layeraction_group_layer' => __('Layer Actions', 'revslider'),
			'layeraction_group_media' => __('Media Actions', 'revslider'),
			'layeraction_group_fullscreen' => __('Fullscreen Actions', 'revslider'),
			'layeraction_group_advanced' => __('Advanced Actions', 'revslider'),
			'layeraction_menu' => __('Menu Link & Scroll', 'revslider'),
			'layeraction_link' => __('Simple Link', 'revslider'),
			'layeraction_callback' => __('Call Back', 'revslider'),
			'layeraction_modal' => __('Open Slider Modal', 'revslider'),
			'layeraction_scroll_under' => __('Scroll below Slider', 'revslider'),
			'layeraction_scrollto' => __('Scroll To ID', 'revslider'),
			'layeraction_jumpto' => __('Jump to Slide', 'revslider'),
			'layeraction_next' => __('Next Slide', 'revslider'),
			'layeraction_prev' => __('Previous Slide', 'revslider'),
			'layeraction_next_frame' => __('Next Frame', 'revslider'),
			'layeraction_prev_frame' => __('Previous Frame', 'revslider'),
			'layeraction_pause' => __('Pause Slider', 'revslider'),
			'layeraction_resume' => __('Play Slide', 'revslider'),
			'layeraction_close_modal' => __('Close Slider Modal', 'revslider'),
			'layeraction_open_modal' => __('Open Slider Modal', 'revslider'),
			'layeraction_toggle_slider' => __('Toggle Slider', 'revslider'),
			'layeraction_start_in' => __('Go to 1st Frame ', 'revslider'),
			'layeraction_start_out' => __('Go to Last Frame', 'revslider'),
			'layeraction_start_frame' => __('Go to Frame "N"', 'revslider'),
			'layeraction_toggle_layer' => __('Toggle 1st / Last Frame', 'revslider'),
			'layeraction_toggle_frames' => __('Toggle "N/M" Frames', 'revslider'),
			'layeraction_start_video' => __('Start Media', 'revslider'),
			'layeraction_stop_video' => __('Stop Media', 'revslider'),
			'layeraction_toggle_video' => __('Toggle Media', 'revslider'),
			'layeraction_mute_video' => __('Mute Media', 'revslider'),
			'layeraction_unmute_video' => __('Unmute Media', 'revslider'),
			'layeraction_toggle_mute_video' => __('Toggle Mute Media', 'revslider'),
			'layeraction_toggle_global_mute_video' => __('Toggle Mute All Media', 'revslider'),
			'layeraction_togglefullscreen' => __('Toggle Fullscreen', 'revslider'),
			'layeraction_gofullscreen' => __('Enter Fullscreen', 'revslider'),
			'layeraction_exitfullscreen' => __('Exit Fullscreen', 'revslider'),
			'layeraction_simulate_click' => __('Simulate Click', 'revslider'),
			'layeraction_toggle_class' => __('Toggle Class', 'revslider'),
			'layeraction_none' => __('Disabled', 'revslider'),
			'backgroundvideo' => __('Background Video', 'revslider'),
			'videoactiveslide' => __('Video in Active Slide', 'revslider'),
			'firstvideo' => __('Video in Active Slide', 'revslider'),
			'triggeredby' => __('Behavior', 'revslider'),
			'addaction' => __('Add Action to ', 'revslider'),
			'ol_images' => __('Images', 'revslider'),
			'ol_layers' => __('Layer Objects', 'revslider'),
			'ol_objects' => __('Objects', 'revslider'),
			'ol_modules' => __('Own Modules', 'revslider'),
			'ol_fonticons' => __('Font Icons', 'revslider'),
			'ol_moduletemplates' => __('Module Templates', 'revslider'),
			'ol_videos' => __('Videos', 'revslider'),
			'ol_svgs' => __('SVG\'s', 'revslider'),
			'ol_favorite' => __('Favorites', 'revslider'),
			'installed' => __('Installed', 'revslider'),
			'notinstalled' => __('Not Installed', 'revslider'),
			'setupnotes' => __('Setup Notes', 'revslider'),
			'requirements' => __('Requirements', 'revslider'),
			'installedversion' => __('Installed Version', 'revslider'),
			'cantpulllinebreakoutside' => __('Use LineBreaks only in Columns', 'revslider'),
			'availableversion' => __('Available Version', 'revslider'),
			'installpackage' => __('Installing Template Package', 'revslider'),
			'installtemplate' => __('Install Template', 'revslider'),
			'installingtemplate' => __('Installing Template', 'revslider'),
			'search' => __('Search', 'revslider'),
			'publish' => __('Publish', 'revslider'),
			'unpublish' => __('Unpublish', 'revslider'),
			'slidepublished' => __('Slide Published', 'revslider'),
			'slideunpublished' => __('Slide Unpublished', 'revslider'),
			'layerpublished' => __('Layer Published', 'revslider'),
			'layerunpublished' => __('Layer Unpublished', 'revslider'),
			'folderBIG' => __('FOLDER', 'revslider'),
			'moduleBIG' => __('MODULE', 'revslider'),
			'objectBIG' => __('OBJECT', 'revslider'),
			'packageBIG' => __('PACKAGE', 'revslider'),
			'thumbnail' => __('Thumbnail', 'revslider'),
			'imageBIG' => __('IMAGE', 'revslider'),
			'videoBIG' => __('VIDEO', 'revslider'),
			'iconBIG' => __('ICON', 'revslider'),
			'svgBIG' => __('SVG', 'revslider'),
			'fontBIG' => __('FONT', 'revslider'),
			'redownloadTemplate' => __('Re-Download Online', 'revslider'),
			'createBlankPage' => __('Create Blank Page', 'revslider'),
			'please_wait_a_moment' => __('Please Wait a Moment', 'revslider'),
			'changingscreensize' => __('Changing Screen Size', 'revslider'),
			'qs_headlines' => __('Headlines', 'revslider'),
			'qs_content' => __('Content', 'revslider'),
			'qs_buttons' => __('Buttons', 'revslider'),
			'qs_bgspace' => __('BG & Space', 'revslider'),
			'qs_shadow' => __('Shadow', 'revslider'),
			'qs_shadows' => __('Shadow', 'revslider'),
			'saveslide' => __('Saving Slide', 'revslider'),
			'loadconfig' => __('Loading Configuration', 'revslider'),
			'updateselects' => __('Updating Lists', 'revslider'),
			'lastslide' => __('Last Slide', 'revslider'),
			'textlayers' => __('Text Layers', 'revslider'),
			'globalLayers' => __('Global Layers', 'revslider'),
			'slidersettings' => __('Slider Settings', 'revslider'),
			'animatefrom' => __('Animate From', 'revslider'),
			'animateto' => __('Keyframe #', 'revslider'),
			'transformidle' => __('Transform Idle', 'revslider'),
			'enterstage' => __('Anim From', 'revslider'),
			'leavestage' => __('Anim To', 'revslider'),
			'onstage' => __('Anim To', 'revslider'),
			'keyframe' => __('Keyframe', 'revslider'),
			'notenoughspaceontimeline' => __('Not Enough space between Frames.', 'revslider'),
			'framesizecannotbeextended' => __('Frame Size can not be Extended. Not enough Space.', 'revslider'),
			'backupTemplateLoop' => __('Loop Template', 'revslider'),
			'backupTemplateLayerAnim' => __('Animation Template', 'revslider'),
			'choose_image' => __('Choose Image', 'revslider'),
			'choose_video' => __('Choose Video', 'revslider'),
			'slider_revolution_shortcode_creator' => __('Slider Revolution Shortcode Creator', 'revslider'),
			'shortcode_generator' => __('Shortcode Generator', 'revslider'),
			'please_add_at_least_one_layer' => __('Please add at least one Layer.', 'revslider'),
			'shortcode_parsing_successfull' => __('Shortcode parsing successfull. Items can be found in step 3', 'revslider'),
			'shortcode_could_not_be_correctly_parsed' => __('Shortcode could not be parsed.', 'revslider'),
			'addonrequired' => __('Addon Required', 'revslider'),
			'licencerequired' => __('Activate License', 'revslider'),
			'searcforicon' => __('Search Icons...', 'revslider'),
			'savecurrenttemplate' => __('Save Current Template', 'revslider'),
			'overwritetemplate' => __('Overwrite Template ?', 'revslider'),
			'deletetemplate' => __('Delete Template ?', 'revslider'),
			'credits' => __('Credits', 'revslider'),
			'notinstalled' => __('Not Installed', 'revslider'),
			'enabled' => __('Enabled', 'revslider'),
			'global' => __('Global', 'revslider'),
			'install_and_activate' => __('Install Add-On', 'revslider'),
			'install' => __('Install', 'revslider'),
			'enableaddon' => __('Enable Add-On', 'revslider'),
			'disableaddon' => __('Disable Add-On', 'revslider'),
			'enableglobaladdon' => __('Enable Global Add-On', 'revslider'),
			'disableglobaladdon' => __('Disable Global Add-On', 'revslider'),
			'sliderrevversion' => __('Slider Revolution Version', 'revslider'),
			'checkforrequirements' => __('Check Requirements', 'revslider'),
			'activateglobaladdon' => __('Activate Global Add-On', 'revslider'),
			'activateaddon' => __('Activate Add-On', 'revslider'),
			'activatingaddon' => __('Activating Add-On', 'revslider'),
			'enablingaddon' => __('Enabling Add-On', 'revslider'),
			'addon' => __('Add-On', 'revslider'),
			'installingaddon' => __('Installing Add-On', 'revslider'),
			'disablingaddon' => __('Disabling Add-On', 'revslider'),
			'buildingSelects' => __('Building Select Boxes', 'revslider'),
			'warning' => __('Warning', 'revslider'),
			'blank_page_added' => __('Blank Page Created', 'revslider'),
			'blank_page_created' => __('Blank page has been created:', 'revslider'),
			'visit_page' => __('Visit Page', 'revslider'),
			'edit_page' => __('Edit Page', 'revslider'),
			'closeandstay' => __('Close', 'revslider'),
			'changesneedreload' => __('The changes you made require a page reload!', 'revslider'),
			'saveprojectornot ' => __('Save your project & reload the page or cancel', 'revslider'),
			'saveandreload' => __('Save & Reload', 'revslider'),
			'canceldontreload' => __('Cancel & Reload Later', 'revslider'),
			'saveconfig' => __('Save Configuration', 'revslider'),
			'updatingaddon' => __('Updating', 'revslider'),
			'addonOnlyInSlider' => __('Enable/Disable Add-On on Module', 'revslider'),
			'openQuickEditor' => __('Open Quick Content Editor', 'revslider'),
			'openQuickStyleEditor' => __('Open Quick Style Editor', 'revslider'),
			'sortbycreation' => __('Sort by Creation', 'revslider'),
			'creationascending' => __('Creation Ascending', 'revslider'),
			'sortbytitle' => __('Sort by Title', 'revslider'),
			'titledescending' => __('Title Descending', 'revslider'),
			'updatefromserver' => __('Update List', 'revslider'),
			'audiolibraryloading' => __('Audio Wave Library is Loading...', 'revslider'),
			'editModule' => __('Edit Module', 'revslider'),
			'editSlide' => __('Edit Slide', 'revslider'),
			'showSlides' => __('Show Slides', 'revslider'),
			'openInEditor' => __('Open in Editor', 'revslider'),
			'openFolder' => __('Open Folder', 'revslider'),
			'moveToFolder' => __('Move to Folder', 'revslider'),
			'loadingcodemirror' => __('Loading CodeMirror Library...', 'revslider'),
			'lockunlocklayer' => __('Lock / Unlock Selected', 'revslider'),
			'nrlayersimporting' => __('Layers Importing', 'revslider'),
			'nothingselected' => __('Nothing Selected', 'revslider'),
			'layerwithaction' => __('Layer with Action', 'revslider'),
			'imageisloading' => __('Image is Loading...', 'revslider'),
			'importinglayers' => __('Importing Layers...', 'revslider'),
			'triggeredby' => __('Triggered By', 'revslider'),
			'import' => __('Imported', 'revslider'),
			'layersBIG' => __('LAYERS', 'revslider'),
			'intinheriting' => __('Responsivity', 'revslider'),
			'changesdone_exit' => __('The changes you made will be lost!', 'revslider'),
			'exitwihoutchangesornot' => __('Are you sure you want to continue?', 'revslider'),
			'areyousuretoexport' => __('Are you sure you want to export ', 'revslider'),
			'areyousuretodelete' => __('Are you sure you want to delete ', 'revslider'),
			'areyousuretodeleteeverything' => __('Delete All Sliders and Folders included in ', 'revslider'),
			'leavewithoutsave' => __('Leave without Save', 'revslider'),
			'updatingtakes' => __('Updating the Plugin may take a few moments.', 'revslider'),
			'exportslidertxt' => __('Downloading the Zip File may take a few moments.', 'revslider'),
			'exportslider' => __('Export Slider', 'revslider'),
			'yesexport' => __('Yes, Export Slider', 'revslider'),
			'yesdelete' => __('Yes, Delete Slider', 'revslider'),
			'yesdeleteslide' => __('Yes, Delete Slide', 'revslider'),
			'yesdeleteall' => __('Yes, Delete All Slider(s)', 'revslider'),
			'stayineditor' => __('Stay in Edior', 'revslider'),
			'redirectingtooverview' => __('Redirecting to Overview Page', 'revslider'),
			'leavingpage' => __('Leaving current Page', 'revslider'),
			'ashtmlexport' => __('as HTML Document', 'revslider'),
			'preparingdatas' => __('Preparing Data...', 'revslider'),
			'loadingcontent' => __('Loading Content...', 'revslider'),
			'copy' => __('Copy', 'revslider'),
			'paste' => __('Paste', 'revslider'),
			'framewait' => __('WAIT', 'revslider'),
			'frstframe' => __('1st Frame', 'revslider'),
			'lastframe' => __('Last Frame', 'revslider'),
			'onlyonaction' => __('on Action', 'revslider'),
			'cannotbeundone' => __('This action can not be undone !!', 'revslider'),
			'deleteslider' => __('Delete Slider', 'revslider'),
			'deleteslide' => __('Delete Slide', 'revslider'),
			'deletingslide' => __('This can be Undone only within the Current session.', 'revslider'),
			'deleteselectedslide' => __('Are you sure you want to delete the selected Slide:', 'revslider'),
			'cancel' => __('Cancel', 'revslider'),
			'addons' => __('Add-Ons', 'revslider'),
			'deletingsingleslide' => __('Deleting Slide', 'revslider'),
			'lastslidenodelete' => __('"Last Slide in Module. Can not be deleted"', 'revslider'),
			'deletingslider' => __('Deleting Slider', 'revslider'),
			'active_sr_tmp_obl' => __('Template & Object Library', 'revslider'),
			'active_sr_inst_upd' => __('Instant Updates', 'revslider'),
			'active_sr_one_on_one' => __('1on1 Support', 'revslider'),
			'parallaxsettoenabled' => __('Parallax is now generally Enabled', 'revslider'),
			'timelinescrollsettoenabled' => __('Scroll Based Timeline is now generally Enabled', 'revslider'),
			'feffectscrollsettoenabled' => __('Filter Effect Scroll is now generally Enabled', 'revslider'),
			'nolayersinslide' => __('Slide has no Layers', 'revslider'),
			'leaving' => __('Changes that you made may not be saved.', 'revslider'),
			'sliderasmodal' => __('Add Slider as Modal', 'revslider'),
			'register_to_unlock' => __('Register to unlock all Premium Features', 'revslider'),
			'premium_features_unlocked' => __('All Premium Features unlocked', 'revslider'),
			'tryagainlater' => __('Please try again later', 'revslider'),
			'quickcontenteditor' => __('Quick Content Editor', 'revslider'),
			'module' => __('Module', 'revslider'),
			'quickstyleeditor' => __('Quick Style Editor', 'revslider'),
			'all' => __('All', 'revslider'),
			'active_sr_to_access' => __('Register Slider Revolution<br>to Unlock Premium Features', 'revslider'),
			'membersarea' => __('Members Area', 'revslider'),
			'onelicensekey' => __('1 License Key per Website!', 'revslider'),
			'onepurchasekey' => __('1 Purchase Code per Website!', 'revslider'),
			'onelicensekey_info' => __('If you want to use your license key on another domain, please<br> deregister it in the members area or use a different key.', 'revslider'),
			'onepurchasekey_info' => __('If you want to use your purchase code on<br>another domain, please deregister it first or', 'revslider'),
			'registeredlicensekey' => __('Registered License Key', 'revslider'),
			'registeredpurchasecode' => __('Registered Purchase Code', 'revslider'),
			'registerlicensekey' => __('Register License Key', 'revslider'),
			'registerpurchasecode' => __('Register Purchase Code', 'revslider'),
			'registerCode' => __('Register this Code', 'revslider'),
			'registerKey' => __('Register this License Key', 'revslider'),
			'deregisterCode' => __('Deregister this Code', 'revslider'),
			'deregisterKey' => __('Deregister this License Key', 'revslider'),
			'active_sr_plg_activ' => __('Register Purchase Code', 'revslider'),
			'active_sr_plg_activ_key' => __('Register License Key', 'revslider'),
			'getpurchasecode' => __('Get a Purchase Code', 'revslider'),
			'getlicensekey' => __('Licensing Options', 'revslider'),
			'ihavepurchasecode' => __('I have a Purchase Code', 'revslider'),
			'ihavelicensekey' => __('I have a License Key', 'revslider'),
			'enterlicensekey' => __('Enter License Key', 'revslider'),
			'enterpurchasecode' => __('Enter Purchase Code', 'revslider'),
			'colrskinhas' => __('This Skin use', 'revslider'),
			'deleteskin' => __('Delete Skin', 'revslider'),
			'references' => __('References', 'revslider'),
			'colorwillkept' => __('The References will keep their colors after deleting Skin.', 'revslider'),
			'areyousuredeleteskin' => __('Are you sure to delete Color Skin?', 'revslider'),


		);

		return $this->_frameworkHelper->apply_filters('revslider_get_javascript_multilanguage', $lang);
	}


	/**
	 * returns all image sizes that have the same aspect ratio, rounded on the second
	 * @since: 6.1.4
	 **/
	public function get_same_aspect_ratio_images($images){
		$return = array();
		$images = (array)$images;

		if(!empty($images)){
			$objlib = new RevSliderObjectLibrary($this->_frameworkHelper);
			$upload_dir = $this->_frameworkHelper->wp_upload_dir();

			foreach($images as $key => $image){
				//check if we are from object library
				if($objlib->_is_object($image)){
					$_img = $image;
					$image = $objlib->get_correct_size_url($image, 100, true);
					$objlib->_check_object_exist($image); //check to redownload if not downloaded yet

					$sizes = $objlib->get_sizes();
					$return[$key] = array();

					if(!empty($sizes)){
						foreach($sizes as $size){
							$url = $objlib->get_correct_size_url($image, $size);
							$file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
							$_size = getimagesize($file);
							$return[$key][$size] = array(
								'url'	=> $url,
								'width'	=> $this->get_val($_size, 0),
								'height'=> $this->get_val($_size, 1),
								'size'	=> filesize($file)
							);

							if($_img === $url) $return[$key][$size]['default'] = true;
						}

						//$image = $objlib->get_correct_size_url($image, 100, true);
						$file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image);
						$_size = getimagesize($file);
						$return[$key][100] = array(
							'url'	=> $image,
							'width'	=> $this->get_val($_size, 0),
							'height'=> $this->get_val($_size, 1),
							'size'	=> filesize($file)
						);
						if($_img === $return[$key][100]['url']) $return[$key][100]['default'] = true;
					}
				}else{
					$_img = (intval($image) === 0) ? $this->get_image_id_by_url($image) : $image;
					$img_data = $this->_frameworkHelper->wp_get_attachment_metadata($_img);

					if(!empty($img_data)){
						$return[$key] = array();
						$ratio = round($this->get_val($img_data, 'width', 1) / $this->get_val($img_data, 'height', 1), 2);
						$sizes = $this->get_val($img_data, 'sizes', array());
						$file = $upload_dir['basedir'] .'/'. $this->get_val($img_data, 'file');
						$return[$key]['full'] = array(
							'url'	=> $upload_dir['baseurl'] .'/'. $this->get_val($img_data, 'file'),
							'width'	=> $this->get_val($img_data, 'width'),
							'height'=> $this->get_val($img_data, 'height'),
							'size'	=> filesize($file)
						);
						if ($this->_frameworkHelper->getImagesHelper()->image_to_url($image) === $return[$key]['full']['url']) $return[$key]['full']['default'] = true;

						if(!empty($sizes)){
							foreach($sizes as $sn => $sv){
								$_ratio = round($this->get_val($sv, 'width', 1) / $this->get_val($sv, 'height', 1), 2);
								if($_ratio === $ratio){
									$i = $this->_frameworkHelper->wp_get_attachment_image_src($_img, $sn);
									if($i === false) continue;

									$file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $this->get_val($i, 0));
									$return[$key][$sn] = array(
										'url'	=> $this->get_val($i, 0),
										'width'	=> $this->get_val($sv, 'width'),
										'height'=> $this->get_val($sv, 'height'),
										'size'	=> filesize($file)
									);
									if($image === $return[$key][$sn]['url']) $return[$key][$sn]['default'] = true;
								}
							}
						}
					}else{
						//either external URL or not available anymore in the media library
					}
				}
			}
		}

		return $return;
	}

	/**
	 * returns all files plus sizes of JavaScript and css files used by the AddOns
	 * @since. 6.1.4
	 **/
	public function get_addon_sizes($addons){
		$sizes = array();

		if(empty($addons) || !is_array($addons)) return $sizes;

		$_css = '/public/assets/css/';
		$_js = '/public/assets/js/';
		//these are the sizes before the AddOns where updated
		$_a = array(
			'revslider-404-addon' => array(),
			'revslider-backup-addon' => array(),
			'revslider-beforeafter-addon' => array(
				$_css .'revolution.addon.beforeafter.css' => 3512,
				$_js .'revolution.addon.beforeafter.min.js' => 21144
			),
			'revslider-bubblemorph-addon' => array(
				$_css .'revolution.addon.bubblemorph.css' => 341,
				$_js .'revolution.addon.bubblemorph.min.js' => 11377
			),
			'revslider-domain-switch-addon' => array(),
			'revslider-duotonefilters-addon' => array(
				$_css .'revolution.addon.duotone.css' => 11298,
				$_js .'revolution.addon.duotone.min.js' => 1232
			),
			'revslider-explodinglayers-addon' => array(
				$_css .'revolution.addon.explodinglayers.css' => 704,
				$_js .'revolution.addon.explodinglayers.min.js' => 19012
			),
			'revslider-featured-addon' => array(),
			'revslider-filmstrip-addon' => array(
				$_css .'revolution.addon.filmstrip.css' => 843,
				$_js .'revolution.addon.filmstrip.min.js' => 5409
			),
			'revslider-gallery-addon' => array(),
			'revslider-liquideffect-addon' => array(
				$_css .'revolution.addon.liquideffect.css' => 606,
				$_js .'pixi.min.js' => 514062,
				$_js .'revolution.addon.liquideffect.min.js' => 11899
			),
			'revslider-login-addon' => array(),
			'revslider-maintenance-addon' => array(),
			'revslider-paintbrush-addon' => array(
				$_css .'revolution.addon.paintbrush.css' => 676,
				$_js .'revolution.addon.paintbrush.min.js' => 6841
			),
			'revslider-panorama-addon' => array(
				$_css .'revolution.addon.panorama.css' => 1823,
				$_js .'three.min.js' => 504432,
				$_js .'revolution.addon.panorama.min.js' => 12909
			),
			'revslider-particles-addon' => array(
				$_css .'revolution.addon.particles.css' => 668,
				$_js .'revolution.addon.particles.min.js' => 33963
			),
			'revslider-polyfold-addon' => array(
				$_css .'revolution.addon.polyfold.css' => 900,
				$_js .'revolution.addon.polyfold.min.js' => 5125
			),
			'revslider-prevnext-posts-addon' => array(),
			'revslider-refresh-addon' => array(
				$_js .'revolution.addon.refresh.min.js' => 920
			),
			'revslider-rel-posts-addon' => array(),
			'revslider-revealer-addon' => array(
				$_css .'revolution.addon.revealer.css' => 792,
				$_css .'revolution.addon.revealer.preloaders.css' => 14792,
				$_js .'revolution.addon.revealer.min.js' => 7533
			),
			'revslider-sharing-addon' => array(
				$_js .'revslider-sharing-addon-public.js' => 6232
			),
			'revslider-slicey-addon' => array(
				$_js .'revolution.addon.slicey.min.js' => 4772
			),
			'revslider-snow-addon' => array(
				$_js .'revolution.addon.snow.min.js' => 4823
			),
			'revslider-template-addon' => array(),
			'revslider-typewriter-addon' => array(
				$_css .'typewriter.css' => 233,
				$_js .'revolution.addon.typewriter.min.js' => 8038
			),
			'revslider-weather-addon' => array(
				$_css .'revslider-weather-addon-icon.css' => 3699,
				$_css .'revslider-weather-addon-public.css' => 483,
				$_css .'weather-icons.css' => 31082,
				$_js .'revslider-weather-addon-public.js' => 5335
			),
			'revslider-whiteboard-addon' => array(
				$_js .'revolution.addon.whiteboard.min.js' => 10649
			)
		);

		//AddOns can apply/modify the default data here
		$_a = $this->_frameworkHelper->apply_filters('revslider_create_slider_page', $_a, $_css, $_js, $this);

		foreach($addons as $addon){
			if(!isset($_a[$addon])) continue;
			$sizes[$addon] = 0;
			if(!empty($_a[$addon])){
				foreach($_a[$addon] as $size){
					$sizes[$addon] += $size;
				}
			}
			//$sizes[$addon] = $_a[$addon];
		}

		return $sizes;
	}

	/**
	 * returns a list of found compressions
	 * @since. 6.1.4
	 **/
	public function compression_settings(){
		$match	= array();
		$com	= array('gzip', 'compress', 'deflate', 'br'); //'identity' -> means no compression prefered
		$enc	= $this->get_val($_SERVER, 'HTTP_ACCEPT_ENCODING');

		if(empty($enc)) return $match;

		foreach($com as $c){
			if(strpos($enc, $c) !== false) $match[] = $c;
		}

		return $match;
	}

}

<?php
$search_sections = array();

$course_count = 0;
$term_names = array();

if (sizeof(@$courses) > 0) {
	foreach ($courses as $sub) {
		foreach ($sub as $s) {
			//echo "<pre>"; print_r($s); echo "</pre>";
			$course_count += sizeof($s);
		}
	}
}

$search_sections[] = array(
	'label' => 'School/College',
	'data' => $facet_options['schools'],
	//'uri_segment' => sizeof($this->uri->segment_array()) - 3
	'uri_segment' => (count($controller_args) + 3)
);

$search_sections[] = array(
  'label' => 'Term',
  'data' => $facet_options['terms'],
  'uri_segment' => (count($controller_args) + 4)
);

$search_sections[] = array(
	'label' => 'Year',
	'data' => $facet_options['years'],
	'uri_segment' => (count($controller_args) + 5)
);

$search_sections[] = array(
	'label' => 'dScribe2',
	'data' => $facet_options['dscribe2s'],
	'uri_segment' => (count($controller_args) + 6)
);

$search_sections[] = array(
	'label' => 'dScribe',
	'data' => $facet_options['dscribe1s'],
	'uri_segment' => (count($controller_args) + 7)
);

$fscrumbs = array();
$view_uri_array = $this->uri->segment_array();

/* uri segment variable, where 3 is the first uri segment that is
 * an argument
 */
$uri_segment = 3;

// tack on the default passed arguments
foreach ($controller_args as $arg_val) {
  $view_uri_array[$uri_segment++] = $arg_val;
}

//if (!isset($view_uri_array[3])) $view_uri_array[3] = $this->db_session->userdata('id'); //prime the faceted search
// $view_uri_array[3] = $this->db_session->userdata('id'); //prime the faceted search


foreach ($search_sections as $ss) {
	$view_uri_array[$ss['uri_segment']] = array_key_exists($ss['uri_segment'], $view_uri_array) ? $view_uri_array[$ss['uri_segment']] : 0;	
}
$view_uri_string = site_url().implode("/",$view_uri_array);
//echo "<pre>"; print_r($view_uri_array); echo "</pre>";

if (sizeof($this->uri->segment_array()) >= sizeof($search_sections)) {	 //less than number of params in url, no faceted search yet
	foreach ($search_sections as $ss) {
		if ($this->uri->segment($ss['uri_segment'])) {
			$filterid = $this->uri->segment($ss['uri_segment']);
	 		$segment_array = explode("z", $filterid);
	 		if (sizeof($segment_array) > 1) {			
	 			foreach ($segment_array as $key=>$filter) {
					$remove_uri_array = $this->uri->segment_array();
					$remove_segment_array = $segment_array;
					unset($remove_segment_array[$key]);
					$remove_segment_str = implode("z", $remove_segment_array);
					$remove_uri_array[$ss['uri_segment']] = $remove_segment_str;
					$remove_uri_string = site_url().implode("/",$remove_uri_array);
					$fscrumbs[] = array('id'=>$filter, 'val'=>$ss['data'][$filter], 'removeurl'=>$remove_uri_string, 'label'=>$ss['label']);
	 			}
	 		} else {
	 			$remove_uri_array = $this->uri->segment_array();
				$remove_uri_array[$ss['uri_segment']] = 0;
				$remove_uri_string = site_url().implode("/",$remove_uri_array);
				$fscrumbs[] = array('id'=>$filterid, 'val'=>$ss['data'][$filterid], 'removeurl'=>$remove_uri_string, 'label'=>$ss['label']);
	 		}
		}
	}
}

$ua = $this->uri->segment_array();

// set the rest of the uri segments to 0
$ua[$uri_segment++] = 0;
$ua[$uri_segment++] = 0; 
$ua[$uri_segment++] = 0;
$ua[$uri_segment++] = 0;
$ua[$uri_segment++] = 0;

$removeallurl = site_url().implode("/",$ua);
$fscrumbs_html = <<<htmleoq
		<a href="$removeallurl" title="Remove all filters">Clear All</a>
htmleoq;

foreach ($fscrumbs as $filterarray) {
	$filterid = $filterarray['id'];
	$filtervalue = $filterarray['val'];
	$filterremoveurl = $filterarray['removeurl'];
	$filterlabel = $filterarray['label'];
	$fscrumbs_html .= <<<htmleoq
	<li class="token-input-token">
		<input type="hidden" name="$filterlabel$filterid" id="$filterlabel$filterid" value="$filterid" />
		<p>$filtervalue</p>
		<a href="$filterremoveurl" title="Remove this filter">x</a>
	</li>
htmleoq;
}
?>

<div class="column span-24 first last">
	<h4 class="faceted_search_title">Courses filtered by:</h4>
	<ul class="token-input-list">
		<?= $fscrumbs_html ?>
	</ul>
</div>

<div class="column span-5 first last">
     <div class="accordion">
  		<h4 class="faceted_search_title"><span id="course_count"><?php echo $course_count; ?></span> courses listed.</h4>

<?php
foreach ($search_sections as $fs_id=>$s) {
?>
		<h4 id="fs_<?php echo $fs_id; ?>_toggler" class="faceted_search_toggler" onclick="fs_<?php echo $fs_id; ?>.toggle()">
	    	<?php echo $s['label']; ?>
	 	</h4>
	 	<div class="faceted_search_element" id="fs_<?= $fs_id; ?>">
	 		<ul class="faceted_search_list">
	 		<?php 
	 		foreach($s['data'] as $dkey=>$d) {
	 			$custom_view_uri_array = $view_uri_array;
	 			$segment_array = explode("z", $custom_view_uri_array[$s['uri_segment']]);
	 			$selectedclass = (in_array($dkey,$segment_array)) ? 'class=selected' : 'class=unselected';
	 			if ($segment_array[0] > 0) {
	 				array_push($segment_array, $dkey);
	 				$custom_view_uri_array[$s['uri_segment']] = implode("z",$segment_array);
	 			} else {	
	 				$custom_view_uri_array[$s['uri_segment']] = $dkey;
	 			}
	 			$custom_view_uri_string = site_url().implode("/",$custom_view_uri_array);
	 			array_pop($segment_array);
		 		if (sizeof($segment_array) > 1) {			
					$remove_uri_array = $this->uri->segment_array();
					$remove_segment_array = $segment_array;
					$foundkey = array_search($dkey, $segment_array);
					unset($remove_segment_array[$foundkey]);
					$remove_segment_str = implode("z", $remove_segment_array);
					$remove_uri_array[$s['uri_segment']] = $remove_segment_str;
					$remove_uri_string = site_url().implode("/",$remove_uri_array);
		 		} else {
		 			$remove_uri_array = $this->uri->segment_array();
					$remove_uri_array[$s['uri_segment']] = 0;
					$remove_uri_string = site_url().implode("/",$remove_uri_array);
		 		}
	 			$link = (in_array($dkey,$segment_array)) ? $remove_uri_string : $custom_view_uri_string;
	 			$selectedx = (in_array($dkey,$segment_array)) ? "<a href=\"$remove_uri_string\" class=\"selectedx\">x</a>" : '';
	 		?>
	 			<li <?= $selectedclass ?>>
	 				<a href="<?= $link ?>"><?= $d; ?></a>
	 				<?= $selectedx ?>
	 			</li>
	 		<?php
	 		}
	 		?>
	 		</ul>
		</div>
		<script>
			fs_<?php echo $fs_id; ?> = new Fx.Slide($('fs_<?php echo $fs_id; ?>'), {
				duration: 200,
				onComplete: function(el) {
					toggler = $(el.id+'_toggler');
					if (toggler.getStyle('background-image') == "url(<?php echo property('app_img'); ?>/expand.gif)") toggler.setStyle('background-image', "url(<?php echo property('app_img'); ?>/collapse.gif)");
					else toggler.setStyle('background-image', "url(<?php echo property('app_img'); ?>/expand.gif)");
				},
				transition: Fx.Transitions.linear 
			}).show();
		</script>
		
<?php		
}
?>	

	</div>
</div>

<div class="column span-1 first last">
	&nbsp;
</div>

// Event rules for OCW Tool 
var Rules = {
	'.confirm' : function(element) {
		element.onclick = function() {
			var con = confirm('Are you sure?');
			return con;
		}
	},

	// utilized on the dscribe manage materials page for updating tags
	'.update_tag' : function(element) {
		element.onchange = function () {
			var response;
			var course_id = $('cid').getValue();
			var tag_id = this.value;
			var material_id = (this.name).replace(/selectname_/g,'');
			var url = $('server').value+'materials/update/'+course_id+'/'+
					  material_id+'/tag_id/'+tag_id;	

			new Ajax(url,
            	{
				 method: 'get',
				 update: $('feedback'),
			 	 onComplete:function(request){
                  	response = $('feedback').innerHTML;
                  	if (response != 'success') { alert(response); }
            	} }).request();
		}
	},

	// update ip object info on manage ip holder page 
	'.update_material' : function(element) {
				element.onchange = function () {
			var response;
			var course_id = $('cid').getValue();
			var material_id = 0;
			if ((this.id).indexOf('inocw_') > -1) {	
				material_id = (this.id).replace(/inocw_\w+_/g,'');
			} else {
				material_id = $('mid').getValue(); 
			}
			var field = ''; 
			if ((this.name).indexOf('in_ocw_') > -1) {	
				field = (this.name).replace(/_\d+/g,'');
			} else {
				field = this.name; 
			}
			var value = this.value; 

			if (field=='author' && value=='') {
				value = $('defcopy').getValue();	
				this.value = value;
			} 

			var check = this.check(field, value);

			if (check=='success') {
				var url = $('server').value+'materials/update/'+course_id+'/'+
						   material_id+'/'+field+'/'+encodeURIComponent(value);	

				new Ajax(url,
            		{ method: 'get',
					  update: $('feedback'),
			 	 	  onComplete:function(request){
                  		response = $('feedback').innerHTML;
                  		if (response != 'success') { alert(response); }
            		} }).request();
			} else {
				alert(check);
			}
		}
        element.check = function(field, val) {
            if (field=='name' && val=='') {
                return('Please enter a descriptive name for the material'); }

            if (field=='author' && val=='') {
                return('Please specify an author'); }
            
						if (field=='category' && val=='') {
                return('Please specify a category name'); }

            return 'success';
        }
	},

	// manage comments 
	'.do_add_material_comment': function(element) {
		element.onclick = function() {
				var course_id = $('cid').value;
				var material_id = $('mid').value; 
      	var url = $('server').value+'materials/add_comment/'+course_id+'/'+material_id;

				var comments = escape($('comments').value);
				if (comments == '') {
                alert('Please enter a comment');
				} else {
                var fb = $('feedback');
                var response;
								url += '/'+encodeURIComponent(comments);

                new Ajax(url,
                    {
					 						method: 'get', 
					 						update: fb,
                     	onComplete:function() {
                        response = fb.innerHTML;
                        if (response=='success') {
                            url = $('server').value+'materials/edit/'+course_id+'/'+material_id;
                            window.location.replace(url);
                        } else {
                            alert(response);
                        }
               }}).request();
			}
		}
	},


	// object functions
	
	
	'.do_add_object_comment': function(element) {
		element.onclick = function(e) {
		  new Event(e).stop();
			var object_id = $('oid').value; 
      var url = $('server').value+'materials/add_object_comment/'+object_id;
			var comments = $('comments').value;
			var get_comments = encodeURIComponent($('comments').value);

			if (comments == '') {
          alert('Please enter a comment');
			} else {
          var fb = $('feedback');
          var response;
					var once = true;
					url += '/'+get_comments; 

          new Ajax(url,
                  {
					 					method: 'get', 
									 	update: fb,
                    onComplete:function() {
                       response = fb.innerHTML;
											if (once) {						
												orig_com_ap.toggle();
                       	if (response=='success') {
														var msg = "<small>by&nbsp;"+$('user').value+"&nbsp;today</small>";
														var line = '<hr style="border: 1px solid #336699"/>';
														var new_line = new Element('p').setHTML(line);
														var new_time = new Element('p').setHTML(msg);
														var new_cm = new Element('p').setHTML(comments);
														new_line.injectTop( $('objectcomments') );
														new_time.injectTop( $('objectcomments') );
														new_cm.injectTop( $('objectcomments') );
														$('comments').value = '';
                        } else {
                            alert(response);
                       	}
												once = false;
					  					}
           }}).request();
			  }
		}
	},
	
	'.do_add_object_question': function(element) {
		element.onclick = function(e) {
		  new Event(e).stop();
			var object_id = $('oid').value; 
      var url = $('server').value+'materials/add_object_question/'+object_id;
			var qs = $('question').value;
			var get_qs = encodeURIComponent($('question').value);
				
			if (qs == '') {
          alert('Please enter a question');
			} else {
          var fb = $('feedback');
          var response;
					var once = true;
					url += '/'+get_qs; 
			
          new Ajax(url,
                  {
					 					method: 'get', 
									 	update: fb,
                    onComplete:function() {
                       response = fb.innerHTML;
											if (once) {						
												orig_q_ap.toggle();
                       	if (response=='success') {
														var msg = "<small>by&nbsp;"+$('user').value+"&nbsp;today</small>";
														var line = '<hr style="border: 1px solid #336699"/>';
														var new_line = new Element('p').setHTML(line);
														var new_time = new Element('p').setHTML(msg);
														var new_cm = new Element('p').setHTML(qs);
														new_line.injectTop( $('objectqs') );
														new_time.injectTop( $('objectqs') );
														new_cm.injectTop( $('objectqs') );
														$('question').value = '';
                        } else {
                            alert(response);
                       	}
												once = false;
					  					}
           }}).request();
			  }
		}
	},

	'.do_object_update' : function(element) {
		element.onchange = function () {
				var response;
				var course_id = $('cid').value;
				var material_id = $('mid').value; 
				var object_id = $('oid').value;
				var field = this.name; 
				var url = $('server').value+'materials/update_object/'+course_id+'/'+material_id;
				var val = this.value;
				if (field=='done') { 	
						if ($('ask_status').value=='false' && val==1) {
							 alert('Cannot mark as cleared an object still under review by the instructor!');
							 return false;
						}
				}
				url += '/'+object_id+'/'+field+'/'+encodeURIComponent(val);
      	var fb = $('feedback');
				new Ajax(url, { method: 'get', update: fb, }).request();
		}
		element.onclick = element.onchange;
	},
	
	'.do_object_action_type' : function(element) {
		element.onclick = function() {
			var id = this.id;
			id = id.replace(/\w+_/g,'');
			if (this.value == 'Fair Use') {
				if ($('Fair Use')) {
					$('Fair Use').style.display = 'block';}
				if ($('Permission')) {
					$('Permission').style.display = 'none';}
				if ($('Commission')) {
					$('Commission').style.display = 'none';}
				if ($('Retain')) {
					$('Retain').style.display = 'none';}
			} else if (this.value == 'Permission') {
				if ($('Fair Use')) {
					$('Fair Use').style.display = 'none';	}
				if ($('Permission')) {
					$('Permission').style.display = 'block';}
				if ($('Commission')) {
					$('Commission').style.display = 'none';}
				if ($('Retain')) {
					$('Retain').style.display = 'none';}
			} else if (this.value == 'Commission') {
				if ($('Fair Use')) {
					$('Fair Use').style.display = 'none';	}
				if ($('Permission')) {
					$('Permission').style.display = 'none';}
				if ($('Commission')) {
					$('Commission').style.display = 'block';}
				if ($('Retain')) {
					$('Retain').style.display = 'none';}
			} else if (this.value == 'Retain') {
				if ($('Fair Use')) {
					$('Fair Use').style.display = 'none';	}
				if ($('Permission')) {
					$('Permission').style.display = 'none';}
				if ($('Commission')) {
					$('Commission').style.display = 'none';}
				if ($('Retain')) {
					$('Retain').style.display = 'block';}
			}
			else
			{
				if ($('Fair Use')) {
					$('Fair Use').style.display = 'none';	}
				if ($('Permission')) {
					$('Permission').style.display = 'none';}
				if ($('Commission')) {
					$('Commission').style.display = 'none';}
				if ($('Retain')) {
					$('Retain').style.display = 'none';}
			}
		}
	},
	
	'.do_object_ask_yesno' : function(element) {
		element.onclick = function() {
			var id = this.id;
			id = id.replace(/\w+_/g,'');
			if (this.value == 'yes') {
				if ($('ask_yes')) {
					$('ask_yes').style.display = 'block';	
				} 
			   if ($('ask_yes')) { $('ask_no').style.display = 'none';}
			} else {
				if ($('ask_no')) {
					$('ask_no').style.display = 'block';	
				} 
			   if ($('ask_yes')) { $('ask_yes').style.display = 'none';	}
			}
		}
	},
	
	'.do_object_ask_dscribe2_yesno' : function(element) {
		element.onclick = function() {
			var id = this.id;
			id = id.replace(/\w+_/g,'');
			if (this.value == 'yes') {
				if ($('ask_dscribe2_yes')) {
					$('ask_dscribe2_yes').style.display = 'block';	
				} 
			   if ($('ask_dscribe2_yes')) { $('ask_dscribe2_no').style.display = 'none';}
			} else {
				if ($('ask_dscribe2_no')) {
					$('ask_dscribe2_no').style.display = 'block';	
				} 
			   if ($('ask_dscribe2_yes')) { $('ask_dscribe2_yes').style.display = 'none';	}
			}
		}
	},

	'.do_object_cp_update' : function(element) {
		element.onchange = function () {
			var val = this.value;
			var course_id = $('cid').value;
			var material_id = $('mid').value; 
			var object_id = this.name.replace(/copy_\w+_/g,'');
			var field = this.name.replace(/copy_/g,'');
			field = field.replace(/_\d+$/g,'');
			var url = $('server').value+'materials/update_object_copyright/'+
					  		object_id+'/'+field+'/'+encodeURIComponent(val)+'/original';
      var fb = $('feedback');
      new Ajax(url, {	method: 'get', update: fb}).request();
		}
	},


	'.do_ask_object_update' : function(element) {
		element.onchange = function () {
			var response;
			var course_id = $('cid').value;
			var material_id = $('mid').value; 
			var object_id = this.id.replace(/\w+_/g,'');
			var field = this.name.replace(/_\d+$/g,'');
			var val = this.value;

			if (field == 'who_owns') {
				object_id = this.id;
				object_id = object_id.replace(/\w+_\w+_/g,'');
				field = 'other_copyholder';
			    val = '';
			}
			if (field == 'unique') { field = 'is_unique'; }

			var url = $('server').value+'materials/update_object/'+course_id+'/'+material_id;
			url += '/'+object_id+'/'+field+'/'+encodeURIComponent(val);
            var fb = $('feedback');
            new Ajax(url, { method: 'get', update: fb, }).request();
		}
	},

	'.do_object_status_update' : function(element) {
		element.onclick = function () {
			var val = (this.value).toLowerCase();
			val = (val == 'save for later') ? 'in progress' : val;
			val = (val == 'send to dscribe') ? 'done' : val;
			var course_id = $('cid').value;
			var material_id = $('mid').value; 
			var view = $('view').value; 
			var object_id = this.name.replace(/status_/g,'');
			var url = $('server').value+'materials/update_object/'+course_id+'/'+
					  material_id+'/'+object_id+'/ask_status/'+encodeURIComponent(val);
            var fb = $('feedback');
			var response;
            new Ajax(url, { method: 'get',
							update: fb, 
                     		onComplete:function() {
                        		response = fb.innerHTML;
                        		if (response=='success') {
                            		url = $('server').value+'materials/askforms/'+
										course_id+'/'+material_id+'/'+view;
                            		window.location.replace(url);
                        		} else {
                            		alert(response);
                        		}
							}
			}).request();
		}
	},

	'.do_object_question_update' : function(element) {
		element.onchange = function () {
			var val = this.value;
			var course_id = $('cid').value;
			var material_id = $('mid').value; 
			var object_id = this.name.replace(/q_/g,'');
			var question_id = object_id;
			object_id = object_id.replace(/_\d+$/g,'');
			question_id = question_id.replace(/^\d+_/g,'');
			var url = $('server').value+'materials/update_object_question/'+
					  object_id+'/'+question_id+'/'+encodeURIComponent(val);
            var fb = $('feedback');
            new Ajax(url, {	method: 'get', update: fb}).request();
		}
	},

	'.do_askform_yesno' : function(element) {
		element.onclick = function() {
			var id = this.id;
			id = id.replace(/\w+_/g,'');
			if ($('other_'+id) && this.value == 'no') {
				$('other_'+id).style.display = 'block';	
			} 
			if ($('other_'+id) && this.value == 'yes') {
				$('other_'+id).style.display = 'none';	
			}
		}
	},

	'.do_askform_suityesno' : function(element) {
		element.onclick = function() {
			var id = this.id;
			id = id.replace(/\w+_/g,'');
			if (this.value == 'yes') {
				if ($('suit_yes_other_'+id)) {
					$('suit_yes_other_'+id).style.display = 'block';	
				} 
			   if ($('suit_no_other_'+id)) { $('suit_no_other_'+id).style.display = 'none';}
			} else {
				if ($('suit_no_other_'+id)) {
					$('suit_no_other_'+id).style.display = 'block';	
				} 
			   if ($('suit_yes_other_'+id)) { $('suit_yes_other_'+id).style.display = 'none';	}
			}
		}
	},

	'.do_askform_whoyesno' : function(element) {
		element.onclick = function() {
			var id = this.id;
			id = id.replace(/\w+_\w+_/g,'');
			if (this.value == 'yes') {
				if ($('who_yes_other_'+id)) {
					  $('who_yes_other_'+id).style.display = 'block';	
				} 
				if ($('who_no_other_'+id)) {
					$('who_no_other_'+id).style.display = 'block';	
				} 
			} else {
				if ($('who_no_other_'+id)) {
					  $('who_no_other_'+id).style.display = 'block';	
				} 
			   if ($('who_yes_other_'+id)) { $('who_yes_other_'+id).style.display = 'none';	}
			}
		}
	},

	// replacement form
	'.do_replacement_update' : function(element) {
		element.onchange = function () {
			var response;
			var course_id = $('cid').value;
			var material_id = $('mid').value; 
			var object_id = this.name; 
		  object_id = object_id.replace(/^\w+_/g,'');
			var field = this.name; 
		  field = field.replace(/_\d+/g,'');
			var val = this.value;

			if (field=='rep_ok') { 
				object_id = this.id;
				object_id = object_id.replace(/repok_\w+_/g,'');
				field = 'suitable';
			}
			if (field == 'notsuitable') {
				object_id = this.id;
				object_id = object_id.replace(/c_/g,'');
				field = 'unsuitable_reason';
			}
			var url = $('server').value+'materials/update_replacement/'+course_id+'/'+material_id;
			url += '/'+object_id+'/'+field+'/'+encodeURIComponent(val);
     	var fb = $('feedback');
     	new Ajax(url, { method: 'get', update: fb, }).request();
		}
	},

	'.do_add_replacement_comment': function(element) {
		element.onclick = function(e) {
		  new Event(e).stop();
			var object_id = $('oid').value; 
      var url = $('server').value+'materials/add_object_comment/'+object_id;
			var comments = $('repl_comments').value;
			var get_comments = encodeURIComponent($('repl_comments').value);
				
			if (comments == '') {
          alert('Please enter a comment');
			} else {
          var fb = $('feedback');
          var response;
					var once = true;
					url += '/'+get_comments+'/replacement'; 

          new Ajax(url,
                  {
					 					method: 'get', 
									 	update: fb,
                    onComplete:function() {
                       response = fb.innerHTML;
											if (once) {						
												repl_com_ap.toggle();
                       	if (response=='success') {
														var msg = "<small>by&nbsp;"+$('user').value+"&nbsp;today</small>";
														var line = '<hr style="border: 1px solid #336699"/>';
														var new_line = new Element('p').setHTML(line);
														var new_time = new Element('p').setHTML(msg);
														var new_cm = new Element('p').setHTML(comments);
														new_line.injectTop( $('replcomments') );
														new_time.injectTop( $('replcomments') );
														new_cm.injectTop( $('replcomments') );
														$('repl_comments').value = '';
                        } else {
                            alert(response);
                       	}
												once = false;
					  					}
           }}).request();
			  }
		}
	},
	
	'.do_add_replacement_question': function(element) {
		element.onclick = function(e) {
		  new Event(e).stop();
			var object_id = $('oid').value; 
      var url = $('server').value+'materials/add_object_question/'+object_id;
			var qs = $('repl_question').value;
			var get_qs = encodeURIComponent($('repl_question').value);
				
			if (qs == '') {
          alert('Please enter a question');
			} else {
          var fb = $('feedback');
          var response;
					var once = true;
					url += '/'+get_qs+'/replacement'; 
			
          new Ajax(url,
                  {
					 					method: 'get', 
									 	update: fb,
                    onComplete:function() {
                       response = fb.innerHTML;
											if (once) {						
												repl_q_ap.toggle();
                       	if (response=='success') {
														var msg = "<small>by&nbsp;"+$('user').value+"&nbsp;today</small>";
														var line = '<hr style="border: 1px solid #336699"/>';
														var new_line = new Element('p').setHTML(line);
														var new_time = new Element('p').setHTML(msg);
														var new_cm = new Element('p').setHTML(qs);
														new_line.injectTop( $('replqs') );
														new_time.injectTop( $('replqs') );
														new_cm.injectTop( $('replqs') );
														$('repl_question').value = '';
                        } else {
                            alert(response);
                       	}
												once = false;
					  					}
           }}).request();
			  }
		}
	},
	
	'.do_replacement_question_update' : function(element) {
		element.onchange = function () {
			var val = this.value;
			var course_id = $('cid').value;
			var material_id = $('mid').value; 
			var object_id = this.name.replace(/q_/g,'');
			var question_id = object_id;
			object_id = object_id.replace(/_\d+$/g,'');
			question_id = question_id.replace(/^\d+_/g,'');
			var url = $('server').value+'materials/update_object_question/'+
					  object_id+'/'+question_id+'/'+encodeURIComponent(val)+'/replacement';
      var fb = $('feedback');
      new Ajax(url, {	method: 'get', update: fb}).request();
		}
	},

	'.do_replacement_cp_update' : function(element) {
		element.onchange = function () {
			var val = this.value;
			var course_id = $('cid').value;
			var material_id = $('mid').value; 
			var object_id = this.name.replace(/copy_\w+_/g,'');
			var field = this.name.replace(/copy_/g,'');
			field = field.replace(/_\d+$/g,'');
			var url = $('server').value+'materials/update_object_copyright/'+
					  		object_id+'/'+field+'/'+encodeURIComponent(val)+'/replacement';
      var fb = $('feedback');
      new Ajax(url, {	method: 'get', update: fb}).request();
		}
	},

	'.do_replacement_status_update' : function(element) {
		element.onclick = function () {
			var val = (this.value).toLowerCase();
			val = (val == 'save for later') ? 'in progress' : val;
			val = (val == 'send to dscribe') ? 'done' : val;
			var course_id = $('cid').value;
			var material_id = $('mid').value; 
			var view = $('view').value; 
			var object_id = this.name.replace(/status_/g,'');
			var url = $('server').value+'materials/update_replacement/'+course_id+'/'+
					  material_id+'/'+object_id+'/ask_status/'+encodeURIComponent(val);
            var fb = $('feedback');
			var response;
            new Ajax(url, { method: 'get',
							update: fb, 
                     		onComplete:function() {
                        		response = fb.innerHTML;
                        		if (response=='success') {
                            		url = $('server').value+'materials/askforms/'+
										course_id+'/'+material_id+'/'+view;
                            		window.location.replace(url);
                        		} else {
                            		alert(response);
                        		}
							}
			}).request();
		}
	},

	// hide and show add panel
	'.do_show_hide_panel' : function (element) {
        element.onclick = function() {
					var panel = $('addpanel');
					var disp = panel.style.display;
					panel.style.display = (disp=='none') ? 'block' : 'none';
        }
    },

	// ASKFORM actions
	'#questions_to' : function (element) {
			element.onchange = function() {
							var cid = $('cid').value;
							var mid = $('mid').value; 
							var view = $('view').value; 
							var val = this.value;
							view = (val=='instructor') ? 'provenance' : 'general';
              url = $('server').value+'materials/askforms/'+cid+'/'+mid+'/'+view+'/'+val;
             	window.location.replace(url);
			}
	},
}

// Remove/Comment this if you do not wish to reapply Rules automatically
// on Ajax request.
//Ajax.Responders.register({
// onComplete: function() { EventSelectors.assign(Rules);}
//});

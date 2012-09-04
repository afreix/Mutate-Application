$(document).ready(function(){

	//Gets the current rotation angle of an element with matrix math
	function getRotationDegrees(obj) {
		var matrix = obj.css("-webkit-transform") ||
		obj.css("-moz-transform")    ||
		obj.css("-ms-transform")     ||
		obj.css("-o-transform")      ||
		obj.css("transform");
		if(matrix !== 'none') {
			var values = matrix.split('(')[1].split(')')[0].split(',');
			var a = values[0];
			var b = values[1];
			var angle = Math.round(Math.atan2(b, a) * (180/Math.PI));
		} else { var angle = 0; }
		return angle;
	}

	var iFrequency = 100; // expressed in miliseconds
	var myIntervals = new Array(); //Hash of output id's to id values from setInterval()

	// STARTS the loop
	function startRotateLoop(object) {
		myInterval[$(object).closest('.preset').attr('data-id')] = setInterval( function(){rotate(object)}, iFrequency );  // run
	}

	//Rotates the icon in processes list
	function rotate(object) {
		if($(object).length > 0) {
			angle = getRotationDegrees(object) + 18;
			$(object).css('transform', "rotate("+angle+"deg)");
			$(object).css('-ms-transform', "rotate("+angle+"deg)");
			$(object).css('-moz-transform', "rotate("+angle+"deg)");
			$(object).css('-o-transform', "rotate("+angle+"deg)");
			$(object).css('-webkit-transform', "rotate("+angle+"deg)");
		} else {
			clearInterval(myInterval[$(object).closest('li').attr('data-id')]);
		}
	}

	/* 
	 * Called upon hovering over an input file
	 * Generates the transcode button and the dropdown menu of the allowed presets
	 */
	function createDropDown(object) {
	    if($("#transcode").length < 1) {
			$(object).find(".btn-group").append("<a id='transcode' class='btn dropdown-toggle' data-toggle='dropdown' href='#'>Transcode<span class='caret'></span></a><ul class='dropdown-menu presets'></ul>");
			var infile = $(object).attr('name');
			/* Uses data sent from InputOutputController -> twig -> here
			 * For each infile key in the array there is a preset and then an extension value (null if it needs to be provided)
			 * Each preset li in the dropdown has an 'ext' (extension) attribute denoting the extension or if it needs to be provided
			 */
			if (App.preset_data[infile].length > 0) {
				for (var i=0; i < App.preset_data[infile].length; i+=2) {
					$(object).find(".dropdown-menu").append("<li class='transcodable' ext='"+App.preset_data[infile][i+1]+"'><a>"+ App.preset_data[infile][i] +"</a></li>");
				}
			} else { //If the input file has no allowed presets
				$(object).find(".dropdown-menu").append("<li><a>There are no presets compatible with this input file.</a></li>");
			}
		}
	};
	function removeDropDown(object) {
		$(object).find(".btn-group").text("");
	};
	
	
	//Parameter variables are set
	var popoverParams = {
		title: "Outfile Data",
		content: "I'm sorry the outfile data couldn't be found.",
		delay: {show:500, hide:100},
	};
	//End of setting parameter variables
	
	$(".sortable").sortable();
	$(".outfile").popover(popoverParams);
	
	/******* Hover effects for infiles/outfiles *******/
	$(".outfile").live("mouseenter", function(){
		$(this).css({
			"font-weight":"bold",
			"background-color": "white",
		});
		
	});
	$(".outfile").live("mouseleave", function(){
		$(this).css({
			"font-weight":"inherit",
			"background-color": "transparent",
		});
	});
	$(".infile").bind("mouseenter", function(){
		createDropDown(this);
		$(this).css({
			"font-weight":"bold",
			"background-color": "white",
		});
	});
	$(".infile").bind("mouseleave", function(){
		removeDropDown(this);
		$(this).css({
			"font-weight":"inherit",
			"background-color": "transparent",
		});
	});
	/*******End Hover effects for infiles/outfiles *******/
	
	/******* Transcoding ajax calls ********/
	$(".presets li").live('click',function(){
		if($(this).hasClass('transcodable')){
			var infile = $(this).closest(".infile").attr("name");
			var preset_key = $(this).text();
			if($(this).attr('ext') == 'null') { //If an extension needs to be entered create a modal for the user
				$("#alert_placeholder").html("<div class='modal fade in'><div class='modal-header'><button class='close'>x</button><h3>Output File Extension</h3></div><div class='modal-body'>Please only type in the output file extension: <input id='ext' type='text' placeholder='Extension for the output file'></input></div><div class='modal-footer'><button class='continue_transcode btn btn-success' infile='"+infile+"' preset_key='"+preset_key+"' type='submit'>Submit and Transcode</button></div>");
				$("#alert_placeholder").css('display','block');
			} else { //Otherwise, create alert making sure they want to proceed
				$("#alert_placeholder").html("<div class='alert alert-info'><button class='close' type='button'>x</button><h4>Transcode File</h4><p>Would you like to transcode "+infile+" with the preset "+preset_key+"?</p><a class='continue_transcode btn btn-success' infile='"+infile+"' preset_key='"+preset_key+"' href='#'>Proceed</a></div>");
				$("#alert_placeholder").css('display','block');
			}
		}
	});
		
    //If the user clicked 'proceed' or 'submit and transcode'		
	$(".continue_transcode").live('click', function(){
		var infile = $(this).attr('infile');
		var preset_key = $(this).attr('preset_key');
		var input_text = $(this).parent('.modal-footer').siblings('.modal-body').children('input');
		if(input_text.length > 0) { //If an extension was supplied by the user entering into the modal
			var outfile_extension = input_text.val();
		} else {
			var outfile_extension = 'null';
		}
		$("#alert_placeholder").html("");
		$("#alert_placeholder").css('display','none');
		$.ajax({ //Schedule the clicked infile for a transcoding job
			type: 'POST',
			url: App.route_url+'/schedule',
			error: function(jqXHR, textStatus, errorThrown) {
				document.write(jqXHR.responseText);
			},
			data: "infile="+ infile + "&preset_key="+ preset_key + "&outfile_extension="+outfile_extension,
			success: function(data){ 
				var id = data['id'];
				//Removes the 'output\\' part of the file path
				var outfile_name = data['outfile'].substring(data['outfile'].indexOf('\\')+1, data['outfile'].length);
				var img_src = App.base_path+"/assets/images/test.gif";
				//Appends a li for the new transcode process with an id corresponding to the id in the database
				$(".processes").append("<li data-id='"+id+"' class='process'>Transcoding "+infile+" <i class='icon-refresh rotater'></i></li>");
				startRotateLoop($(".processes").find("li:last").children("i"));
				$.ajax({ //Transcode file with given id 
					type: 'POST',
					url: App.route_url+'/transcode',
					error: function(jqXHR, textStatus, errorThrown) {
						document.write(jqXHR.responseText);
					},
					data: "id="+id,
					success: function(data) { //Update the result column in the database and append the outfile li with the correct popover content
						//Remove the process li with the data-id attribute corresponding with the id of the transcoded file
						var processes = $(".processes").children("li");
						$(processes).each(function(index){
							if($(this).attr('data-id') == id) {
								$(this).remove();
							}
						});
						$.ajax({
							type: 'POST',
							url: App.route_url+'/update_retrieve',
							error: function(jqXHR, textStatus, errorThrown) {
								document.write(jqXHR.responseText);
							},
							data: "id="+id+"&outfile="+outfile_name+"&infile="+infile,
							success: function(data) { //Fill the popover with the result data
								 if(data != null) {
									var values = "";
									values += "Infile Name: " + data['infile_name'] +'\n\r<br>';
									values += "Infile Size: " + data['infile_size'] +'\n\r<br>';
									values += "Outfile Name: " + data['outfile_name'] + '\n\r<br>';
									values += "Outfile Size: " + data['outfile_size'];
									$(".outfiles").append("<li class='outfile deletable' id='"+id+"'name='"+outfile_name+"'data-content='"+values+"' data-title='File Information'><a href='output/"+outfile_name+"'>"+outfile_name+"</a><span class='btn-container'><div class='btn-group'></div></span></li>");
									$(".outfiles").find("li:last").popover(popoverParams);
								} else {
									$("#alert_placeholder").html("<div class='alert alert-error'><button class='close'>x</button><h4 class='alert-header'>Error</h4><p>There was an error transcoding your file. If you entered an extension, please check to make sure it was a valid extension.</p></div>");
									$("#alert_placeholder").css('display','block');
								}
							},
							dataType: 'json',
						});
					},
				});
			},
			dataType: 'json',
		});
	});
	/******* Transcoding ajax calls ********/
	
	/******** Deletion javascript **********/
	
	//Appends a delete button when hovering over a deletable
	$(".deletable").live('mouseenter', function(){
		if($("#delete").length < 1) {
			$(this).find(".btn-group").append("<button id='delete' class='btn'>Delete Me!</button>");
		}
	});
	
	//Removes a delete button when hovering out of a deletable
	$(".deletable").live('mouseleave', function(){
		$(this).find("#delete").remove();
		$("body").find(".popover").remove();
	});
	
	//Called when a delete button is click, creates a prompt making sure the user really wants to delete the given file
	$("#delete").live('click', function(){
		var object = $(this).closest(".deletable");
		var file = $(object).attr("name");
		var id = 'null';
		var outfile = false;
		var infile = false;
		if($(object).hasClass('infile')) {
			infile = true;
		} else {
			id = $(object).attr("id");
			outfile = true;
		}
		$("#alert_placeholder").html("<div class='alert alert-block alert-error fade in'><button class='close' type='button'>x</button><h4 class='alert-heading'>Warning!</h4><p>You are about to delete file: "+file+"! This is permanent and cannot be redone.</p><p><a class='continue_delete btn btn-danger' href='#'>Proceed</a></p></div>");
		$("#alert_placeholder").css('display','block');
		//Gives continue delete button the data it needs to complete the deletion
		$(".continue_delete").data('delete_data', {'object': object, 'id': id, 'file': file, 'infile': infile, 'outfile': outfile});
	});
	
	//Removes the alert placeholder
	$(".close").live('click',function(){
		$("#alert_placeholder").html("");
		$("#alert_placeholder").css('display','none');
	});
	$(".continue_delete").live('click',function(){
		var data = $(this).data('delete_data');
		var id = data.id;
		var file = data.file;
		var infile = data.infile;
		var outfile = data.outfile;
		//Lets us know which deletable was clicked, so we can remove it from the html structure
		var object = data.object;
		
		//The alert box is removed
		$("#alert_placeholder").html("");
		$("#alert_placeholder").css('display','none');
		
		$.ajax({ //Deletes the file from the database
			type:'DELETE',
			url: App.route_url+'/delete',
			error: function(jqXHR, textStatus, errorThrown) {
				document.write(jqXHR.responseText);
			},
			data: "id="+id+"&file="+file+"&outfile="+outfile+"&infile="+infile,
			success: function(data) { //Shows an alert showing the deletion was successful
				$("#alert_placeholder").html("<div class='alert alert-info'><button class='close'>x</button><h4 class='alert-header'>Update</h4><p>Your file was successfully deleted!</p></div>");
				$(object).remove();
			},
		});
	});
	/******** End Deletion javascript **********/
	
	/******** Upload File javascript ********/
	$(".upload").change(function(){
		$(".uploads").html("");
		for(var i = 0; i<this.files.length; i++) {
			$(".uploads").append("<li><i class='icon-ok-circle'></i>"+this.files[i].name+"</li>");
		}
	});
	/******** End Upload File javascript *******/
	
	$(".create_preset_modal").click(function(){
		$("#alert_placeholder").html("<div class='modal fade in'><div class='modal-header'><button class='close'>x</button><h3>Create Custom Preset</h3></div><div class='modal-body'>Enter the preset key:<input id='key' type='text' placeholder='Key'></input><br>Enter the required adapter: <input id='adapter' type='text' placeholder='Adapter'></input><br></div><div class='modal-footer'><button class='create_preset btn btn-success' type='submit'>Create Preset</button></div>");
		$("#alert_placeholder").css('display','block');
		var body = $("#alert_placeholder").find(".modal-body");
		$(body).append("Fill out which options you would like for your preset: <br>");
		$(body).append("Enter the audio bitrate you would like: <input option='audio-bitrate' type='text' placeholder='optional'></input><br>");
	});
	$(".create_preset").live('click', function(){
		var input_fields =  $(this).parent('.modal-footer').siblings('.modal-body').children('input');
		var key;
		var adapter;
		var options = {};
		$(input_fields).each(function(index){
			if(index == 0) {
				key = $(this).val();
			} else if(index == 1) {
				adapter = $(this).val();
			} else {
				var id = $(this).attr('option');
				options[id] = $(this).val();
			}
		});
		if (key.length > 0 && adapter.length > 0) {
			$.ajax({
				type: 'POST',
				url: App.route_url+'/createPreset',
				error: function(jqXHR, textStatus, errorThrown) {
					document.write(jqXHR.responseText);
				},
				data: {k:key, a:adapter, o:options},
				//processData: false,
				success: function(data) {
					alert(data);
					//$("#alert_placeholder").html("<div class='alert alert-info'><button class='close'>x</button><h4 class='alert-header'>Congratulations</h4><p>Your custom preset was successfully created!</p><button class='btn btn-success reload'>Reload Page with New Preset</button></div>");
					//$("#alert_placeholder").css('display','block');
				},
			});
		} else {
			alert("You haven't entered enough data to create a custom preset!");
		}
	});
	$(".reload").live('click', function(){
		location.reload();
	});
	
});
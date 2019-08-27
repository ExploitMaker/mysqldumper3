humane.baseCls="humane-jackedup";
humane.clickToClose = true;
humane.timeout = 1800;
var total_time = 0;

function convertMS(ms) {
  var d, h, m, s;
  s = Math.floor(ms / 1000);
  m = Math.floor(s / 60);
  s = s % 60;
  h = Math.floor(m / 60);
  m = m % 60;
  d = Math.floor(h / 24);
  h = h % 24;
  return { d: d, h: h, m: m, s: s };
}

function dump_database(database, current, next) {
	if(current == 0) {
		humane.log("Starting database dump for: "+database);
		$('#log').val('Starting dump on database: '+database);
	}
	var start_time = new Date().getTime();
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {dumpdb:database, current:current, next:next}
	}).done(function(result) {
		var response_time = new Date().getTime() - start_time;
		total_time = total_time + response_time;
		var logval = $('#log').val();
		var json = jQuery.parseJSON(result);
		$('#log').val(logval + json.log);
		$('#log').scrollTop($('#log')[0].scrollHeight);
		$('#db_progress').attr('max', json.numrows);
		$('#db_progress').val(current);
		if(json.status == 'complete') {
			var msconvert = convertMS(total_time);
			$('#complete').html('The database dump was completed in: '+msconvert.h+' hours '+msconvert.m+' minutes '+msconvert.s+' seconds. A download should have appeared already. If it hasn\'t you can find the dumps here:<br><a href="./'+json.filename+'.gz" class="black">Compressed Version</a><br><a href="./'+json.filename+'" class="black">Uncompressed Version</a>');
			$('#complete').css('display', 'block');
			humane.log("Database dump for "+database+" is now complete");
			window.location.replace('./'+json.filename+'.gz');
		} else if(json.status == 'compressing') {
			compress_file(json.filename, json.startingbyte, json.filesize);
		} else if(json.status == 'dumping') {
			dump_database(database, json.current, json.next);
		} else if(json.status == 'splitting') {
			$('#log').val(logval +'\nSplitting table: '+json.table);
			split_table(json.database, json.table, json.filename, json.limit, json.sections, json.current, json.next, false);
		}
	});
}

function dump_table(database, table, splitdone) {
	if(!splitdone) {
		humane.log("Starting table dump for: "+database+"."+table);
		$('#log').val('Starting dump on database: '+database+'.'+table);
	}
	var start_time = new Date().getTime();
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {dump_table:"", database:database, table:table, split_done:splitdone}
	}).done(function(result) {
		var response_time = new Date().getTime() - start_time;
		total_time = total_time + response_time;
		var logval = $('#log').val();
		var json = jQuery.parseJSON(result);
		$('#log').val(logval + json.log);
		$('#log').scrollTop($('#log')[0].scrollHeight);
		if(json.status == 'complete') {
			var msconvert = convertMS(total_time);
			$('#complete').html('The table dump was completed in: '+msconvert.h+' hours '+msconvert.m+' minutes '+msconvert.s+' seconds. A download should have appeared already. If it hasn\'t you can find the dumps here:<br> <a href="./'+json.filename+'.gz" class="black">Compressed Version</a><br><a href="./'+json.filename+'" class="black">Uncompressed Version</a>');
			$('#complete').css('display', 'block');
			humane.log("Table dump for "+database+"."+table+" is now complete");
			window.location.replace('./'+json.filename+'.gz');
		} else if(json.status == 'compressing') {
			compress_file(json.filename, json.startingbyte, json.filesize);
		} else if(json.status == 'dumping') {
			var msconvert = convertMS(total_time);
			$('#complete').html('The table dump was completed in: '+msconvert.h+' hours '+msconvert.m+' minutes '+msconvert.s+' seconds. A download should have appeared already. If it hasn\'t you can find the dumps here:<br> <a href="./'+json.filename+'.gz" class="black">Compressed Version</a><br><a href="./'+json.filename+'" class="black">Uncompressed Version</a>');
			$('#complete').css('display', 'block');
			humane.log("Database dump for "+database+"."+table+" is now complete");
			window.location.replace('./'+json.filename+'.gz');
		} else if(json.status == 'splitting') {
			$('#log').val(logval +'\nSplitting table: '+json.table);
			split_table(json.database, json.table, json.filename, json.limit, json.sections, json.current, json.next, true);
		}
	});
}

function split_table(database, table, filename, limit, sections, current, next, onlytable) {
	var start_time = new Date().getTime();
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {split_table:table, db:database, filename:filename, limit:limit, sections:sections, current:current, next:next, onlytable:onlytable}
	}).done(function(result) {
		var response_time = new Date().getTime() - start_time;
		total_time = total_time + response_time;
		var logval = $('#log').val();
		var json = jQuery.parseJSON(result);
		$('#log').val(logval + json.log);
		$('#log').scrollTop($('#log')[0].scrollHeight);
		if(json.status == 'dumping') {
			if(!json.table_only) {
				$('#table_progress').attr('max', 0);
				$('#table_progress').val(0);
				dump_database(database, current, next);
			} else {
				dump_table(database, table, true);
			}
		} else if(json.status == 'splitting') {
			$('#table_progress').attr('max', sections);
			$('#table_progress').val(json.limit);
			split_table(database, table, filename, json.limit, sections, current, next, onlytable);
		}
	});
}

function compress_file(filename, startingbyte, filesize) {
	var start_time = new Date().getTime();
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {compress_file:filename, startingbyte:startingbyte, filesize:filesize}
	}).done(function(result) {
		var response_time = new Date().getTime() - start_time;
		total_time = total_time + response_time;
		var logval = $('#log').val();
		var json = jQuery.parseJSON(result);
		$('#log').val(logval + json.log);
		$('#log').scrollTop($('#log')[0].scrollHeight);
		if(json.status == 'compressing') {
			$('#compression_progress').attr('max', filesize);
			$('#compression_progress').val(json.startingbyte);
			compress_file(json.filename, json.startingbyte, json.filesize);
		} else if(json.status == 'complete') {
			var msconvert = convertMS(total_time);
			$('#complete').html('The database dump was completed in: '+msconvert.h+' hours '+msconvert.m+' minutes '+msconvert.s+' seconds. A download should have appeared already. If it hasn\'t you can find the dumps here:<br><a href="./'+json.filename+'.gz" class="black">Compressed Version</a><br><a href="./'+json.filename+'" class="black">Uncompressed Version</a>');
			$('#complete').css('display', 'block');
			window.location.replace('./'+json.filename+'.gz');
		}
	});
}

function drop_database(database) {
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {dropdb:database}
	}).done(function(result) {
		var json = jQuery.parseJSON(result);
		if(json.status == 'success') {
			humane.log("Dropped database: "+database);
			$("#database_"+database).remove();
		} else if(json.status == 'fail') {
			humane.log("Failed to drop: "+database);
		}
	});
}

function delete_row(table, database, value, column, rowid) {
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {delete_row:"",table:table,database:database,column:column,value:value}
	}).done(function(result) {
		var json = jQuery.parseJSON(result);
		if(json.status == 'success') {
			humane.log("Deleted row!");
			$("#row_"+rowid).remove();
		} else if(json.status == 'fail') {
			humane.log("Failed to delete row!");
		}
	});
}

function delete_dump(filename, rowid) {
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {delete_dump:"",filename:filename}
	}).done(function(result) {
		var json = jQuery.parseJSON(result);
		if(json.status == 'success') {
			humane.log("Deleted dump!");
			$("#row_"+rowid).remove();
		} else if(json.status == 'fail') {
			humane.log("Failed to delete dump!");
		}
	});
}

function refresh_processes() {
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {refresh_processes:""}
	}).done(function(result) {
		$('#processes').html(result);
	});
}

function kill_process(processid, rowid) {
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {kill_process:processid}
	}).done(function(result) {
		var json = jQuery.parseJSON(result);
		if(json.status == 'success') {
			humane.log("Killed process!");
			$("#row_"+rowid).remove();
		} else if(json.status == 'fail') {
			humane.log("Failed to kill process!");
		}
	});
}

function update_table_row(database) {
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {fetch_table_row:database}
	}).done(function(result) {
		$('#tables_row').html(result);
		$('#table_select').change(function(){
			$('#format_row').css('display', 'table-row');
			$('#separator_row').css('display', 'table-row');
			$('#submit_row').css('display', 'table-row');
			$('#format_row').nextUntil('#separator_row').remove();
			$('#add_column_button').remove();
		});
		$('#format_select').change(function(){
			if($(this).val() == 'custom') {
				add_column_row(database, $('#table_select').val(), false, true);
				$('#submit_cell').append("<button id='add_column_button' onclick=\"add_column_row('"+database+"', '"+$('#table_select').val()+"', false, false);\">Add Custom Column</button>");
			} else {
				// Reset everything below the format select
				$('#format_row').nextUntil('#separator_row').remove();
				$('#add_column_button').remove();
			}
		});
	});
}

function add_column_row(database, table, directlytoend, first) {
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {fetch_column_row:"",database:database,table:table,first:first}
	}).done(function(result) {
		if(directlytoend) {
			$('#column_dumper_table > tbody:last').append(result);
		} else {
			$('#separator_row').before(result);
		}
	});
}

function parse_column_data() {
	var database = $('#database_select').val();
	var table = $('#table_select').val();
	var format = $('#format_select').val();
	var separator = $('#separator').val();
	
	if(format == 'custom') {
		var columnsarray = new Array();
		$('.column_select').each(function() {
			//alert($(this).val());
			columnsarray.push($(this).val());
		});
		custom_column_dump(database, table, separator, columnsarray);
	} else {
		//Formatted dumps will be here eventually.
	}
}


function custom_column_dump(database, table, separator, customcolumns) {
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {
			custom_column_dump:"",
			database:database,
			table:table,
			separator:separator,
			customcolumns:customcolumns
		}
	}).done(function(result) {
		var json = jQuery.parseJSON(result);
		
		if(json.status == 'splitting') {
			$('#column_dumper').html("<div id='complete'></div><br>Column Dump Progress:<br><progress id='column_dump_progress' max='"+json.sections+"' value=''></progress><br>Compression Progress:<br><progress id='compression_progress' max='' value=''></progress><br>Log:<br><textarea id='log' cols='110' rows='20' readonly></textarea><br>");
			$('#log').html('Started column dump on '+database+'.'+table+' columns: '+customcolumns.join(',')+'\n');
			split_custom_column_dump(database, table, separator, customcolumns, json.limit, json.sections, json.filename);
		} else if(json.status == 'complete') {
			humane.log("Database dump for "+database+" is now complete");
			window.location.replace('./'+json.filename+'.gz');
		}
	});
}

function split_custom_column_dump(database, table, separator, customcolumns, limit, sections, filename) {
	$.ajax({
		type: "POST",
		url: "./ajax.php",
		data: {
			split_custom_column_dump:"",
			database:database,
			table:table,
			separator:separator,
			customcolumns:customcolumns,
			limit:limit,
			sections:sections,
			filename:filename
		}
	}).done(function(result) {
		var json = jQuery.parseJSON(result);
		if(json.status == 'splitting') {
			$('#column_dump_progress').val(json.limit);
			var logval = $('#log').val();
			$('#log').val(logval + json.log);
			$('#log').scrollTop($('#log')[0].scrollHeight);
			split_custom_column_dump(database, table, separator, customcolumns, json.limit, sections, filename);
		} else if(json.status == 'compressing') {
			compress_file(json.filename, json.startingbyte, json.filesize);
		} else if(json.status == 'complete') {
			var logval = $('#log').val();
			$('#log').val(logval + json.log);
			$('#log').scrollTop($('#log')[0].scrollHeight);
			humane.log("Database dump for "+database+" is now complete");
			window.location.replace('./'+json.filename+'.gz');
		}
	});
}

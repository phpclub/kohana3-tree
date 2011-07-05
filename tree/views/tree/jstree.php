<div id="mmenu" style="height:30px; overflow:auto;">
	<input type="button" id="add" value="add" style="display:block; float:left;"/>
	<input type="button" id="rename" value="rename" style="display:block; float:left;"/>
	<input type="button" id="remove" value="remove" style="display:block; float:left;"/>
</div>
<div id="demo" class="demo" style="height:500px;"></div>
<script type="text/javascript">
	$(function () {
		new jstree_manager({'tree':'#demo','add_button':'#add','rename_button':'#rename','remove_button':'#remove'},
				   '<?=$root_id?>',<?=$json_tree?>);
	});
</script>
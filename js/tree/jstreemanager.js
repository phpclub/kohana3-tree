/**
 * Basic actions manager for jstree plugin
 * @link http://www.jstree.com/documentation
 * @author Alexey Geno <alexeygeno@gmail.com>
 */
jstree_manager = function(selectors,root_id,json_data) {
	this.init(selectors,root_id,json_data);
}

//extending jstree_manager prototype
$.extend(jstree_manager.prototype, {
	/**
	 * Initialize tree and action buttons
	 * @param hash Required selectors {'tree':'#tree','add_button':'#add','rename_button':'#rename','remove_button':'#remove'}
	 * @param string Root node id
	 * @param array Json data to fill in tree
	 */
	init: function(selectors,root_id,json_data) {

		this.root_id = root_id;
		//defines dom links by selectors
		this.tree = $(selectors.tree);
		this.add_button = $(selectors.add_button);
		this.rename_button = $(selectors.rename_button);
		this.remove_button = $(selectors.remove_button);

		//binds actions
		this.add_button.bind('click', {parent_: this}, function(e) {
			e.data.parent_.add();
		});

		this.rename_button.bind('click', {parent_: this}, function(e) {
			e.data.parent_.rename();
		});

		this.remove_button.bind('click', {parent_: this}, function(e) {
			e.data.parent_.remove();
		});

		//Initializes, fills in and defines based actions of tree
		this.create(json_data);

	},

	/**
	 * Initializes, fills in and defines based actions of tree	 
	 */
	create: function(json_data) {
		this.tree.jstree({
		// List of active plugins
		"plugins" : [
			"themes","json_data","ui","crrm","dnd","types",
		],
		//crrm plugin settings
		"crrm" : {
				move : {
					// Only inside moving is alllowed
					check_move : function(m){
						if (m.p!='inside') {
							return false;
						}
						else {
							return true;
						}
					},
					default_position:'inside'

				}
		},
		//fills in tree
		"json_data" : {	
			"data" : json_data
		},
		
		//crrm plugin settings
		"ui" : {
			//root is selected by default
			"initially_select" : [ this.root_id ]
		}
		})
		//expand all by default
		.bind("loaded.jstree", function (event, data) {
			$(this).jstree("open_all");
		})
		//creating new node
		.bind("create.jstree", function (e, data) {
		//sends chenges to server
			$.ajax({
				async : false,
				type: 'POST',
				url: "/tree/add",
				data :  {
						"destination_parent_id" : data.rslt.parent.attr("id"),
						"name": data.rslt.name
					},
					dataType:'json',
					success : function (response) {

						if(!response.success) {
							$.jstree.rollback(data.rlbk);
						}else
						{
							$(data.rslt.obj).attr("id", response.data.id);
						}
					}
			});
		})
		//removing node
		.bind("remove.jstree", function (e, data) {
			data.rslt.obj.each(function () {
				//sends changes to server
				$.ajax({
					async : false,
					type: 'POST',
					url: "/tree/remove",
					data : {
						"id" : this.id
					},
					dataType:'json',
					success : function (response) {

						if(!response.success) {
							$.jstree.rollback(data.rlbk);
						}
					}
				});
			});
		})
		//renaming node
		.bind("rename.jstree", function (e, data) {
			//sends changes to server
			$.ajax({
					async : false,
					type: 'POST',
					url: "/tree/rename",
					data : {
						"id" : data.rslt.obj.attr("id"),
						"name": data.rslt.new_name
					},
					dataType:'json',
					success : function (response) {

						if(!response.success) {
							$.jstree.rollback(data.rlbk);
						}
					}
				});
		})
		//moving node
		.bind("move_node.jstree", function (e, data) {
			data.rslt.o.each(function (i) {
				//sends changes to server
				$.ajax({
					async : false,
					type: 'POST',
					url: "/tree/move",
					data : {
						"source_parent_id" : $(this).attr("id"),
						"destination_parent_id" : data.rslt.np.attr("id")

					},
					dataType:'json',
					success : function (response) {
						if(!response.success) {
							$.jstree.rollback(data.rlbk);
						}
					}
				});
			});
		});
   	},

	/**
	 * Initiate add action for tree
	 */
	add:function(){
	   this.tree.jstree("create", null, "first");
	},

	/**
	 * Initiate remove action for tree
	 */
	remove:function(){
	   this.tree.jstree("remove");
	},

	/**
	 * Initiate rename action for tree
	 */
	rename:function(){
	   this.tree.jstree("rename");
	}
});
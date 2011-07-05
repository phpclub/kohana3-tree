<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Binds Model to client side library jstree
 *
 * @link http://www.jstree.com/
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Tree
 */
class Tree_JSTree {

	/**
	 * Model Of tree
	 * @var object
	 */
	protected $_model;

	/**
	 * Creates new instance, initialize model
	 */
	public function __construct()
	{
		$this->_model = new Model_Tree_NestedSet();
	}

	/**
	 * Reformats list with level depht given by model to hierarhical structure
	 *
	 * Array
	 *	(
	 *		[0] => Array
	 *			(
	 *			[data] => Root
	 *			[attr] => Array
	 *				(
	 *				[id] => 1
	 *				)
	 *
	 *			[level] => 0
	 *			[children] => Array
	 *				(
	 *				[0] => Array
	 *					(
	 *					[data] => child1 of root
	 *					[attr] => Array
	 *						(
	 *						[id] => 100
	 *						)
	 *
	 *					[level] => 1
	 *					[children] => Array
	 *						(
	 *						)
	 *
	 *					)
	 *		 		)
	 *			)
	 *	)
	 *
	 * @return array
	 */
	protected function get()
	{
		$list = $this->_model->get_list();

		// Trees mapped
		$trees = array();
		$l = 0;

		if (count($list) > 0)
		{
			// Node Stack. Used to help building the hierarchy
			$stack = array();

			foreach ($list as $node)
			{
				$item = $node;
				$item['children'] = array();
				// Number of stack items
				$l = count($stack);

				// Check if we're dealing with different levels
				while($l > 0 && $stack[$l - 1]['level'] >= $item['level'])
				{
					array_pop($stack);
					$l--;
				}

				//apply jstree structure
				$jstree_item = array('data'=>$item['name'],
							   'attr'=>array('id'=>$item['id']),
							   'level'=>$item['level'],
							   'children'=>$item['children']);

				// Stack is empty (we are inspecting the root)
				if ($l == 0)
				{
					// Assigning the root node
					$i = count($trees);
					
					$trees[$i] = $jstree_item;
					$stack[] = & $trees[$i];
				}
				else
				{
					// Add node to parent
					$i = count($stack[$l - 1]['children']);
					$stack[$l - 1]['children'][$i] = $jstree_item;
					$stack[] = & $stack[$l - 1]['children'][$i];
				}
			}
		}
		return $trees;
	}

	/**
	 * Removes node and all its childs with result in json string
	 * {'success':true|false}
	 * @param string Node id
	 * @return string
	 */
	public function remove($id)
	{
		$result = array('success'=>$this->_model->remove($id));
		return json_encode($result);
	}

	/**
	 * Adds new node with result in json string
	 * {'success':true|false,'data':{'id':1}}
	 * @param string Parent node id for new node - node will be added as first child of it
	 * @param string Name of new node
	 * @return string
	 */
	public function add($destination_parent_id,$name)
	{
		$new_node_id = $this->_model->add($destination_parent_id, $name);
		$result = array('success'=>$new_node_id!==FALSE,'data'=>array('id'=>$new_node_id));
		return json_encode($result);
	}

	/**
	 * Moves node and all its childs as first child of another node with result in json string
	 * {'success':true|false}
	 * @param string Id of top node of moving branch
	 * @param string Parent node id for new place of branch - top of branch will be added as first child of it
	 * @return string
	 */
	public function move($source_parent_id, $destination_parent_id)
	{
		$result = array('success'=>$this->_model->move($source_parent_id, $destination_parent_id));
		return json_encode($result);
	}

	/**
	 * Renames node with result in json string
	 * {'success':true|false}
	 * @param string Node Id
	 * @param string New name of node
	 * @return bool
	 */
	public function rename($id, $name)
	{
		$result = array('success'=>$this->_model->rename($id, $name));
		return json_encode($result);
	}

	/**
	 * Magic method
	 * Returns hierarhical structure of tree in json string, should be used to fill jstree
	 * @return string
	 */
	public function  __toString()
	{
		return (string)View::factory('tree/jstree',
						array('json_tree'=> json_encode($this->get()),
							  'root_id'=>Model_Tree_NestedSet::ROOT_ID)
					   );
	}

}
?>

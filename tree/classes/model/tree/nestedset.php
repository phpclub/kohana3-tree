<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Implements based actions on Nested Set Model
 * 
 * @link http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
 * @link http://mikhailstadnik.com/hierarchical-data-structures-and-performance
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Tree
 */
class Model_Tree_NestedSet {

	/**
	 * Root node id
	 * @var int
	 */
	const ROOT_ID = 1;

	/**
	 * Returns full list with level of depth
	 * array(
	 *	array('id'=>1,'name'=>'root',..., 'level'=>0)),
	 *	array('id'=>2,'name'=>'somename',..., 'level'=>1)),
	 *	...
	 * )
	 * @return array
	 */
	public function get_list()
	{
		return DB::query(Database::SELECT,
			'SELECT node.*, (COUNT(parent.id) - 1) AS level
			 FROM ns_tree AS node, ns_tree AS parent
			 WHERE node.lft BETWEEN parent.lft AND parent.rgt
			 GROUP BY node.id
			 ORDER BY node.lft')
		->as_assoc()
		->execute()
		->as_array();
	}

	/**
	 * Returns bool about if node exists or not
	 * @param string Node id
	 * @return bool
	 */
	public function exists ($id)
	{
		$nodes = DB::select()
				->from('ns_tree')
				->where('id','=',$id)
				->as_assoc()
				->execute()
				->as_array();
		if(count($nodes)===1)
		{
			return $nodes[0];
		}
		return FALSE;
	}

	/**
	 * Adds new node, returns added node id or FALSE
	 * @param string Parent node id for new node - node will be added as first child of it
	 * @param string Name of new node
	 * @return bool
	 */
	public function add($destination_parent_id,$name)
	{   
		try
		{
			if($this->exists($destination_parent_id)===FALSE)
			{
				return FALSE;
			}

			DB::query(NULL, 'START TRANSACTION')->execute();
			
			DB::query(Database::SELECT,'SELECT @destination_parent_left := lft FROM ns_tree WHERE id = :destination_parent_id')
				->param(':destination_parent_id',$destination_parent_id)
				->execute();
			//does place for new node
			DB::query(Database::UPDATE,'UPDATE ns_tree SET rgt = rgt + 2 WHERE rgt > @destination_parent_left')
				->execute();
			DB::query(Database::UPDATE,'UPDATE ns_tree SET lft = lft + 2 WHERE lft > @destination_parent_left')
				->execute();
			//inserts new node
			$insert_db_res = DB::query(Database::INSERT,'INSERT INTO ns_tree(name, lft, rgt) VALUES(:name, @destination_parent_left +1, @destination_parent_left +2)')
				->param(':name',$name)
				->execute();

			DB::query(NULL, 'COMMIT')->execute();

			//returns inserted_id
			return $insert_db_res[0];
		}
		catch(Database_Exception $e)
		{
			DB::query(NULL, 'ROLLBACK')->execute();
			return FALSE;
		}

	}

	/**
	 * Renames node
	 * @param string Node Id
	 * @param string New name of node
	 * @return bool
	 */
	public function rename($id,$name)
	{

		return (count(DB::query(Database::UPDATE,'UPDATE ns_tree SET name = :name WHERE id = :id')
			->param(':name', $name)
			->param(':id', $id)
			->execute())===1);
	}

	/**
	 * Removes node and all its childs
	 * @param string Node id
	 * @return bool
	 */
	public function remove($id)
	{
		try
		{
			if($this->exists($id)===FALSE OR $id==self::ROOT_ID)
			{
				return FALSE;
			}

			DB::query(NULL, 'START TRANSACTION')->execute();

			DB::query(Database::SELECT,'SELECT @left := lft, @right := rgt, @width := rgt - lft + 1 FROM ns_tree WHERE id = :id')
				->param(':id',$id)
				->execute();
			//Deletes needed node and its childs
			DB::query(Database::DELETE,'DELETE FROM ns_tree WHERE lft BETWEEN @left AND @right')
				->execute();
			//moves up rest of tree
			DB::query(Database::UPDATE,'UPDATE ns_tree SET rgt = rgt - @width WHERE rgt > @right')
				->execute();
			DB::query(Database::UPDATE,'UPDATE ns_tree SET lft = lft - @width WHERE lft > @right')
				->execute();

			DB::query(NULL, 'COMMIT')->execute();

			return TRUE;
		}
		catch(Database_Exception $e)
		{
			DB::query(NULL, 'ROLLBACK')->execute();
			return FALSE;
		}
	}

	/**
	 * Moves node and all its childs as first child of another node
	 * @param string Id of top node of moving branch
	 * @param string Parent node id for new place of branch - top of branch will be added as first child of it
	 * @return bool
	 */
	public function move($source_parent_id,$destination_parent_id)
	{
		try
		{
			if($this->exists($source_parent_id)===FALSE OR $this->exists($destination_parent_id)===FALSE)
			{
				return FALSE;
			}

			DB::query(NULL, 'START TRANSACTION')->execute();

			//destination_parent_id.lft + 1 is the destination
			DB::query(Database::SELECT,'SELECT @destination := (lft + 1) FROM ns_tree WHERE id = :destination_parent_id')
				->param(':destination_parent_id',$destination_parent_id)
				->execute();
			DB::query(Database::SELECT,'SELECT @source_parent_width := ((rgt - lft) + 1) FROM ns_tree WHERE id = :source_parent_id')
				->param(':source_parent_id',$source_parent_id)
				->execute();

			//Rip this table a new source_parent_id sized hole inside destination_parent_id
			DB::query(Database::UPDATE,'UPDATE ns_tree SET rgt = rgt + @source_parent_width WHERE rgt >= @destination')
				->execute();
			DB::query(Database::UPDATE,'UPDATE ns_tree SET lft = lft + @source_parent_width WHERE lft >= @destination')
				->execute();

			DB::query(Database::SELECT,'SELECT @source_parent_left := lft, @source_parent_right := rgt FROM ns_tree WHERE id = :source_parent_id')
				->param(':source_parent_id',$source_parent_id)
				->execute();

			//Move source_parent_id and all inhabitants to new hole
			DB::query(Database::SELECT,'SELECT @diff := @destination - @source_parent_left')
				->execute();
			DB::query(Database::UPDATE,'UPDATE ns_tree SET rgt = rgt + @diff WHERE rgt BETWEEN @source_parent_left AND @source_parent_right')
				->execute();
			DB::query(Database::UPDATE,'UPDATE ns_tree SET lft = lft + @diff WHERE lft BETWEEN @source_parent_left AND @source_parent_right')
				->execute();

			// Close the gap created when we moved source_parent_id
			DB::query(Database::UPDATE,'UPDATE ns_tree SET rgt = rgt - @source_parent_width WHERE rgt >= @source_parent_left')
				->execute();
			DB::query(Database::UPDATE,'UPDATE ns_tree SET lft = lft - @source_parent_width WHERE lft >= @source_parent_left')
				->execute();

			DB::query(NULL, 'COMMIT')->execute();
			return TRUE;
		}
		catch(Database_Exception $e)
		{
			DB::query(NULL, 'ROLLBACK')->execute();
			return FALSE;
		}
	}
}

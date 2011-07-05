<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Tree controller
 *
 * @author Alexey Geno <alexeygeno@gmail.com>
 * @package Tree
 */
class Controller_Tree extends Controller_Template {

	/**
	 * jstree manager object
	 * @var object
	 */
	protected $_jstree;

	/**
	 * View  page template
	 * @var string
	 */
	public $template = 'tree/demotemplate';

	/**
	 * Loads jstree and disables autorender
	 */
	public function  before()
	{
		parent::before();
		$this->_jstree = new Tree_JSTree();
		$this->auto_render = FALSE;
	}

	/**
	 * Action of main page
	 */
	public function action_index()
	{
		$this->auto_render = TRUE;
		$this->template->jstree = (string)$this->_jstree;
	}

	/**
	 * Add node action
	 */
	public function action_add()
	{
		echo $this->_jstree->add($this->request->post('destination_parent_id'),
						$this->request->post('name'));
	}

	/**
	 * Rename node action
	 */
	public function action_rename()
	{
		echo $this->_jstree->rename($this->request->post('id'),
						$this->request->post('name'));
	}

	/**
	 * Move node action
	 */
	public function action_move()
	{
		echo $this->_jstree->move($this->request->post('source_parent_id'),
						$this->request->post('destination_parent_id'));
	}

	/**
	 * Remove node action
	 */
	public function action_remove()
	{
		echo $this->_jstree->remove($this->request->post('id'));
	}
}

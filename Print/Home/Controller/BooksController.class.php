<?php
namespace Home\Controller;
use Think\Controller;

class BooksController extends Controller
{
	/**
	 * 自己分享的文件列表页
	 * @method index
	 * @author NewFuture[newfuture@yunyin.org]
	 */
	public function index()
	{
		// $uid = use_id();
		// if (!$uid)
		// {
		// 	$this->error('请登录！', U('/'));
		// }

		$books = D('BookView')->Page(1, 20)->cache(600)->select();
		array_walk($books, function (&$book)
		{
			$SCHOOL = array('未知学校', '南开大学', '天津大学', '天津商职');
			$book['school'] = $SCHOOL[$book['school']];
			return $book;});
		$this->books = $books;
		$this->display();
	}

	/**
	 * 分享文件搜
	 * @method search
	 * @param  输入 tid
	 * @author NewFuture[newfuture@yunyin.org]
	 */
	public function search()
	{
		// $uid = use_id();
		// if (!$uid)
		// {
		// 	$this->error('未登录！');
		// }

		$string = I('q');           //字符搜索
		$page   = I('p', 1, 'int'); //翻页
		                            // $pid       = I('pid', 0, 'int');
		$Book      = D('BookView');
		$condition = array();

		if ($string)
		{
			//通过关键字搜索
			$condition['book.name'] = array('LIKE', '%' . strtr($string, ' ', '%') . '%');

		}

		if (!empty($condition))
		{
			$Book->where($condition);
		}

		if ($books = $Book->page($page)->limit(50)->select())
		{
			array_walk($books, function (&$book)
			{
				$SCHOOL = array('未知学校', '南开大学', '天津大学', '天津商职');
				$book['school'] = $SCHOOL[$book['school']];return $book;});
			$this->success($books);
		}
		else
		{
			$this->error('无查询结果╮(╯-╰)╭');
		}
	}

	public function detail($id = 0)
	{

		$uid = use_id();
		if ($id && $book = M('Book')->cache(60)->field('id,pri_id,time,name,price,detail,image')->find($id))
		{
			$this->book = $book;
			if ($uid)
			{
				$this->printer = M('Printer')->cache(600)->field('id,name,address,email,phone,qq,profile,open_time AS open,price,price_more AS more')->find($book['pri_id']);
			}
			$this->display();
		}
		else
		{
			$this->error('不存在');
		}
	}

	public function prints($id = 0)
	{

		if (!$uid = use_id())
		{
			$this->error('请登录后打印！');
		}
		elseif (!$book = M('book')->find($id))
		{
			$this->error('资源不存在');
		}
		elseif (!$copies = I('copies'))
		{
			$this->error('份数大于0');
		}
		else
		{
			$file['pri_id']     = $book['pri_id'];
			$file['use_id']     = $uid;
			$file['status']     = 1;
			$file['ppt_layout'] = 10;
			$file['copies']     = $copies;
			$file['url']        = 'book:' . $book['name'] . '/' . $id;
			$file['name']       = $book['name'] . '【店内书】';
			if ($requirements = I('post.requirements', 0, 'htmlspecialchars'))
			{
				$file['requirements'] = $requirements;
			}
			if (M('file')->add($file))
			{
				$this->success('打印订单设置成功', '/File');
			}
			else
			{
				$this->success('打印失败');
			}
		}
	}

	/**
	 * 店内书
	 * @method p
	 * @return [type]  [description]
	 * @author NewFuture
	 */
	public function p()
	{
		$id            = I('path.2', 0, 'int');
		// var_dump(I('path.2'));
		$books         = M('Book')->where('pri_id=%d', $id)->Page(1, 20)->cache(600)->select();
		$this->books   = $books;
		$this->pid     = $id;
		$this->printer = M('printer')->getFieldById($id, 'name');
		$this->display();
	}

	/**
	 * 分享文件搜
	 * @method search
	 * @param  输入 tid
	 * @author NewFuture[newfuture@yunyin.org]
	 */
	public function searchIn($printer = 0)
	{
		// $uid = use_id();
		// if (!$uid)
		// {
		// 	$this->error('未登录！');
		// }

		$string = I('q'); //字符搜索
		                  // $page      = I('p', 1, 'int'); //翻页
		                  // $pid       = I('pid', 0, 'int');
		$Book      = M('Book');
		$condition = array();

		if ($printer && intval($printer))
		{
			$condition['pri_id'] = $printer;
		}
		else
		{
			$this->error('未选择打印店');
		}

		if ($string)
		{
			//通过关键字搜索
			$condition['book.name'] = array('LIKE', '%' . strtr($string, ' ', '%') . '%');

		}

		if (!empty($condition))
		{
			$Book->where($condition);
		}

		if ($books = $Book->page($page)->limit(50)->select())
		{
			$this->success($books);
		}
		else
		{
			$this->error('无查询结果╮(╯-╰)╭');
		}
	}
}

<?php

/**
 * Page book
 *
 * 使用者帳號頁
 * @package page
 * @author Kaede
 */
class book {
	/**
	 * 取得帳號資訊
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://book.richi.com/book/info
	 * @global string book_id 書籍代碼
	 * @return array error, book
	 */
	public function info() {
		//取得參數
		$book_id = _REQ('book_id');

		//確認帳號
		$book = queryRow('SELECT * FROM book WHERE book_id=?', $book_id);
		if (!$book) {
			return array('error' => 1, 'message' => '沒有這個帳號');
		}
		
		return array(
			'error' => 0,
			'book'  => $book,
		);
	}
}

<?php
	/*
	Plugin Name: WP Auto Category Trackback
	Plugin URI: http://secondrun.xrea.jp/2010/04/wordpress%e3%83%97%e3%83%a9%e3%82%b0%e3%82%a4%e3%83%b3%ef%bc%9awp-auto-category-trackback.html
	Description: This plugin provides a new function that send trackback automatically when a post published in specific category like MT.
	Version: 10.04.30
	Author: FarbiE
	Author URI: http://secondrun.xrea.jp/


    Copyright 2010 FARBIE

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/

	load_plugin_textdomain("wp_auto_category_trackback", false, dirname(plugin_basename(__FILE__))."/languages");

	//記事投稿時にトラックバックを送る
	add_action('publish_post', 'wp_auto_category_trackback');
	add_action('xmlrpc_publish_post ', 'wp_auto_category_trackback');
	//カテゴリ編集フォームにトラックバック送信先の入力欄を追加
	add_action('edit_category_form_fields', 'trackbacks_url');
	//カテゴリ編集が終わった際フォームデータを取得
	add_action('edit_category', 'category_edited');
	
/*	function activation() {
		add_option('CategoryTrackbackURL');
	}
*/
	//トラックバック送信
	function wp_auto_category_trackback($post_id) {
		$post = get_post($post_id);
		$post_title = $post->post_title;
		$post_date = strtotime($post->post_date);
		$post_modified = strtotime($post->post_modified);
		
		//$_POST['post_status']と$_POST['original_post_status']が在り、publish→publishの場合は弾く
		if(isset($_POST['post_status'], $_POST['original_post_status'])) {
			if($_POST['post_status']=='publish' && $_POST['original_post_status']=='publish') {
				return;
			}
		//↑がない場合で、かつpost_date<post_modifiedの場合弾く
		}elseif($post_date<$post_modified) {
			return;
		}
		
		$option = (array)get_option('CategoryTrackbackURL');
		
		foreach((get_the_category($post_id)) as $cat) {
			$post_category = $cat->cat_ID;
			if(array_key_exists($post_category, $option)) {
				$tb_url = $option[$post_category];
				if($tb_url != NULL) {
					trackback_url_list($tb_url, $post_id);
				}
			}
		}
	}
	
	//カテゴリ編集フォーム拡張
	function trackbacks_url($category) {
		$option = (array)get_option('CategoryTrackbackURL');
		if(array_key_exists($category->term_id, $option)) {
			$value = $option[$category->term_id];
		} else {
			$value = "";
		}
		echo "<tr class='form-field'><th scope='row' valign='top'><label for='category_trackbacks'>".__("Trackback URL(comma-separeted)","wp_auto_category_trackback")."</label></th>\n";
		echo "<td><input type='text' name='category_trackbacks' id='category_trackbacks' value='".$value."'/></td></tr>";
	}
	
	//カテゴリ編集が終わったらオプションに反映
	function category_edited($category_id) {
		if(isset($_POST['category_trackbacks'])){
			//アンインストール用
			if($_POST['category_trackbacks']=='**uninstall**') {
				delete_option('CategoryTrackbackURL');
				return;
			}
			$option = (array)get_option('CategoryTrackbackURL');
			$option[$category_id] = $_POST['category_trackbacks'];
			update_option('CategoryTrackbackURL', $option);
		}
	}
	
?>

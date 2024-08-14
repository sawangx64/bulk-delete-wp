<?php
/*
Plugin Name: Bulk Delete Plugin
Description: Plugin untuk menghapus semua postingan, media, tag, kategori, dan berdasarkan kategori.
Version: 1.0
Author: <p><a title="Sawang" href="https://sawang.my.id">Sawang</a></p>
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Menambahkan menu di admin
add_action('admin_menu', 'bdp_add_admin_menu');

function bdp_add_admin_menu() {
    add_menu_page(
        'Bulk Delete Plugin',
        'Bulk Delete',
        'manage_options',
        'bulk-delete-plugin',
        'bdp_settings_page',
        'dashicons-trash'
    );
}

// Menampilkan halaman pengaturan
function bdp_settings_page() {
    ?>
    <div class="wrap">
        <h1>Bulk Delete Plugin</h1>
        <form method="post" action="">
            <?php wp_nonce_field('bdp_action', 'bdp_nonce'); ?>

            <h2>Hapus Semua Postingan</h2>
            <p>
                <input type="submit" name="bdp_delete_posts" value="Hapus Semua Postingan" class="button button-primary" />
            </p>

            <h2>Hapus Semua Media</h2>
            <p>
                <input type="submit" name="bdp_delete_media" value="Hapus Semua Media" class="button button-primary" />
            </p>

            <h2>Hapus Semua Tag</h2>
            <p>
                <input type="submit" name="bdp_delete_tags" value="Hapus Semua Tag" class="button button-primary" />
            </p>

            <h2>Hapus Semua Kategori</h2>
            <p>
                <input type="submit" name="bdp_delete_categories" value="Hapus Semua Kategori" class="button button-primary" />
            </p>

            <h2>Hapus Postingan Berdasarkan Kategori</h2>
            <?php
            $categories = get_categories();
            if (!empty($categories)) {
                echo '<select name="bdp_category_id">';
                foreach ($categories as $category) {
                    echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                }
                echo '</select>';
            }
            ?>
            <p>
                <input type="submit" name="bdp_delete_category_posts" value="Hapus Postingan di Kategori Tersebut" class="button button-primary" />
            </p>
        </form>
    </div>
    <?php
    if (isset($_POST['bdp_delete_posts'])) {
        check_admin_referer('bdp_action', 'bdp_nonce');
        bdp_delete_all_posts();
    }
    if (isset($_POST['bdp_delete_media'])) {
        check_admin_referer('bdp_action', 'bdp_nonce');
        bdp_delete_all_media();
    }
    if (isset($_POST['bdp_delete_tags'])) {
        check_admin_referer('bdp_action', 'bdp_nonce');
        bdp_delete_all_tags();
    }
    if (isset($_POST['bdp_delete_categories'])) {
        check_admin_referer('bdp_action', 'bdp_nonce');
        bdp_delete_all_categories();
    }
    if (isset($_POST['bdp_delete_category_posts']) && !empty($_POST['bdp_category_id'])) {
        check_admin_referer('bdp_action', 'bdp_nonce');
        $category_id = intval($_POST['bdp_category_id']);
        bdp_delete_posts_by_category($category_id);
    }
}

function bdp_delete_all_posts() {
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'post_status' => 'any'
    );
    $posts = get_posts($args);
    foreach ($posts as $post) {
        wp_delete_post($post->ID, true);
    }
    echo '<div class="notice notice-success is-dismissible"><p>Semua postingan telah dihapus.</p></div>';
}

function bdp_delete_all_media() {
    $args = array(
        'post_type' => 'attachment',
        'posts_per_page' => -1
    );
    $attachments = get_posts($args);
    foreach ($attachments as $attachment) {
        wp_delete_attachment($attachment->ID, true);
    }
    echo '<div class="notice notice-success is-dismissible"><p>Semua media telah dihapus.</p></div>';
}

function bdp_delete_all_tags() {
    $tags = get_tags(array('hide_empty' => false));
    foreach ($tags as $tag) {
        wp_delete_term($tag->term_id, 'post_tag');
    }
    echo '<div class="notice notice-success is-dismissible"><p>Semua tag telah dihapus.</p></div>';
}

function bdp_delete_all_categories() {
    $categories = get_categories(array('hide_empty' => false));
    foreach ($categories as $category) {
        if ($category->term_id != get_option('default_category')) { // Skip default category
            wp_delete_term($category->term_id, 'category');
        }
    }
    echo '<div class="notice notice-success is-dismissible"><p>Semua kategori telah dihapus.</p></div>';
}

function bdp_delete_posts_by_category($category_id) {
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'cat' => $category_id
    );
    $posts = get_posts($args);
    foreach ($posts as $post) {
        wp_delete_post($post->ID, true);
    }
    echo '<div class="notice notice-success is-dismissible"><p>Semua postingan di kategori tersebut telah dihapus.</p></div>';
}

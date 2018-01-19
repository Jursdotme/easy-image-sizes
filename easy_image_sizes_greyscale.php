<?php 
add_filter('wp_generate_attachment_metadata', 'easy_image_sizes_bw_filter');
function easy_image_sizes_bw_filter($meta)
{

    $args = array(
        'post_type' => 'easy_image_sizes',
        'meta_key' => 'easy_image_sizes_bw',
        'meta_value' => 'Yes'
    );

    $time = substr($meta['file'], 0, 7); // <- get the correct time of the upload
    $file = wp_upload_dir($time); // <- locates the correct upload directory

    $the_query = new WP_Query($args);
    if ($the_query->have_posts()) {
        $bw_sizes = [];
        while ($the_query->have_posts()) {
            $the_query->the_post();
            array_push($bw_sizes, sanitize_title(get_the_title()));
        }
    }

    foreach ($bw_sizes as $bw_size) {
        $file = trailingslashit($file['path']) . $meta['sizes'][$bw_size]['file'];
        list($orig_w, $orig_h, $orig_type) = @getimagesize($file);
        $image = wp_load_image($file);
        imagefilter($image, IMG_FILTER_GRAYSCALE);
        imagegammacorrect($image, 1.0, apply_filters('grayscale_gamma_correction', 0.7));
        switch ($orig_type) {
            case IMAGETYPE_GIF:
                imagegif($image, $file);
                break;
            case IMAGETYPE_PNG:
                imagepng($image, $file);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($image, $file);
                break;
        }
    }
    return $meta;
}
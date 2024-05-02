<?php
/** no direct access **/
defined('MECEXEC') or die();

?>
<input type="hidden" id="mec-advimp-ajaxurl" value="<?php echo admin_url('admin-ajax.php'); ?>">
<div class="wrap" id="mec-wrap">
    <h1><?php _e('MEC Advanced Importer', 'mec-advanced-importer'); ?></h1>
    <h2 class="nav-tab-wrapper">

        <?php 
        foreach (self::$tabs as $kt => $vtab) {
                        $args = array(
                'page'=>'MEC-advimp',
                'tab'=>"MEC-{$vtab['link']}"
            );
            $url = add_query_arg( $args , admin_url( 'admin.php' ));
            ?>

            <a href="<?php echo $url; ?>" class="nav-tab <?php if($vtab['link']==$tab->name) echo 'nav-tab-active'; ?>">
            <?php _e($vtab['title'],'mec-advanced-importer'); ?>
            </a>
            <?php
        }

         ?>
    </h2>
    <div class="mec-container">
        <div class="import-content w-clearfix extra">
          
            <?php 
            echo $tab->content(); ?>
        </div>
    </div>
</div>
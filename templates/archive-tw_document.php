<?php
/**
 * General Archive File
 *
 * @package WordPress
 * @subpackage Third Wunder
 * @since Third Wunder 1.0
 */
$post_type    = 'tw_document';
$tax_cat      = 'tw_document_category';
$tax_tag      = 'tw_document_tag';
$curr_cat_id  = false;
$curr_cat     = false;
$curr_tax     = false;
$is_document_archive = false;


$curr_cat_id = get_queried_object_id()!==0 ? get_queried_object_id() : false;

if($curr_cat_id){
  if( get_term( $curr_cat_id, $tax_cat ) ){
    $curr_cat = get_term( $curr_cat_id, $tax_cat );
    $curr_tax = $tax_cat;
  }elseif( get_term($curr_cat_id, $tax_tag ) ){
    $curr_cat = get_term( $curr_cat_id, $tax_tag );
    $curr_tax = $tax_tag;
  }
}else{
  $is_document_archive = true;
  $doc_cat_args = array(
      'orderby'           => 'name',
      'order'             => 'ASC',
      'hide_empty'        => true,
  );
  $doc_categories = get_terms( $tax_cat, $doc_cat_args );
}


get_header(); ?>
<!-- Site Container -->

<div id="site-content" class="container-fluid">
  <?php do_action('tw_document_plugin_before_document_archive'); ?>
  <div id="site-container" class="">

    <div id="primary" class="content-area container">
    	<main id="main" class="site-main" role="main" itemprop="mainContentOfPage">
        <div id="page-archive" class="page-archive" <?php echo tw_html_tag_schema(); ?>>

          <header class="page-header">
            <?php do_action('tw_document_plugin_before_document_archive_title'); ?>
            <?php
              the_archive_title( '<h1 class="page-title">', '</h1>' );
              the_archive_description( '<div class="archive-meta">', '</div>' );
            ?>
          </header><!-- .page-header -->

          <?php if (have_posts()): ?>
    			  <section id="document-posts" class="page-posts">

              <?php if($is_document_archive):?>


                <?php
                  if( count($doc_categories)>1):
                    foreach($doc_categories as $doc_category):
                      $doc_args = array (
                      	'post_type'              => array( $post_type ),
                      	'order'                  => 'ASC',
                      	'orderby'                => 'title',
                      	'tax_query' => array(
                      		array(
                      			'taxonomy' => $tax_cat,
                      			'field' => 'slug',
                      			'terms' => $doc_category->slug,
                      		)
                      	)
                      );
                      $doc_query = new WP_Query( $doc_args );
                      if ( $doc_query->have_posts() ):
                      ?>
                        <h3><?php echo $doc_category->name;?></h3>
                        <div class="document-group">
                          <ul class="fa-ul">
                        <?php
                          while ( $doc_query->have_posts() ): $doc_query->the_post();
                            $document = get_post_meta(get_the_id(), 'tw_document_file', true);
                            $document_url = isset($document['url']) ? trim($document['url']) : false;
                            if($document_url):
                              $document_icon = tw_file_icon_from_url($document_url);
                        ?>
                            <li class="">
                              <a href="<?php echo $document_url;?>" title="<?php the_title();?>" target="_blank" download>

                                <i class="fa-li fa <?php echo $document_icon;?>"></i>
                                <?php the_title();?>

                                &nbsp;&nbsp;&nbsp;<button class="btn btn-xs btn-primary"><?php _e('Download','tw-documents-plugin'); ?>  <i class="fa fa-cloud-download fa-fw"></i></button>
                              </a>
                            </li>
                            <?php endif; ?>
                        <?php endwhile; wp_reset_query(); ?>
                          </ul>
                        </div><!-- #document-group -->
                      <?php
                      endif;
                    endforeach;
                ?>




                <?php else: ?>

                  <div class="document-group">
                    <ul class="fa-ul">
                    <?php while (have_posts()): the_post();
                      $document = get_post_meta(get_the_id(), 'tw_document_file', true);
                      $document_url = isset($document['url']) ? trim($document['url']) : false;
                      if($document_url):
                        $document_icon = tw_file_icon_from_url($document_url);
                    ?>
                        <li class="">
                          <a href="<?php echo $document_url;?>" title="<?php the_title();?>" target="_blank" download>

                            <i class="fa-li fa <?php echo $document_icon;?>"></i>
                            <?php the_title();?>

                            <button class="btn btn-xs btn-primary"><?php _e('Download','tw-documents-plugin'); ?>  <i class="fa fa-cloud-download fa-fw"></i></button>
                          </a>
                        </li>
                      <?php endif; ?>
                    <?php endwhile; ?>
                    </ul>
                  </div><!-- glossary-group -->

                <?php endif; ?>



              <?php else: ?>


                <div class="document-group">
                  <ul class="fa-ul">
                  <?php while (have_posts()): the_post();
                    $document = get_post_meta(get_the_id(), 'tw_document_file', true);
                    $document_url = isset($document['url']) ? trim($document['url']) : false;
                    if($document_url):
                      $document_icon = tw_file_icon_from_url($document_url);
                  ?>
                      <li class="">
                        <a href="<?php echo $document_url;?>" title="<?php the_title();?>" target="_blank" download>

                          <i class="fa-li fa <?php echo $document_icon;?>"></i>
                          <?php the_title();?>

                          <button class="btn btn-xs btn-primary"><?php _e('Download','tw-documents-plugin'); ?>  <i class="fa fa-cloud-download fa-fw"></i></button>
                        </a>
                      </li>
                    <?php endif; ?>
                  <?php endwhile; ?>
                  </ul>
                </div><!-- glossary-group -->

              <?php endif; ?>



    			  </section><!-- #document-posts -->
          <?php endif; ?>

          <footer class="page-footer">
            <?php tw_pagination(); ?>
        	</footer><!-- .page-footer -->

        </div><!-- #page-category -->
      </main><!-- .site-main -->
    </div><!-- .content-area -->

  </div><!-- #site-container -->
  <?php do_action('tw_document_plugin_after_document_archive'); ?>
</div><!-- #site-content -->
<?php get_footer(); ?>
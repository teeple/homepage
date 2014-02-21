	</div>
	<div class="clear"></div>
</div><!-- /.columns (#content) -->
<?php 

/**
 * The Footer
 * 
 * lambda framework v 2.1
 * by www.unitedthemes.com
 * since lambda framework v 2.0
 */
 
global $lambda_meta_data, $theme_options;
$metadata = $lambda_meta_data->the_meta();

$footerwidgets = is_active_sidebar('first-footer-widget-area') + is_active_sidebar('second-footer-widget-area') + is_active_sidebar('third-footer-widget-area') + is_active_sidebar('fourth-footer-widget-area');
$class = ($footerwidgets == '0' ? 'noborder' : 'normal'); ?>

<footer id="footer-wrap" class="fluid clearfix">
	<div class="container">
			<div id="footer" class="<?php echo $class; ?> sixteen columns"> 

			<?php //loads sidebar-footer.php
				get_sidebar( 'footer' );
			?>
			</div><!--/#footer-->
           	
    </div><!--/.container-->
</footer><!--/#footer-wrap-->
            
			
<div id="sub-footer-wrap" class="clearfix">
				<div class="container">
                <div class="copyright">
               	
                    
                <img src="/ko/wp-content/uploads/2013/04/footer_uangel1.png" /> 
   
                </div>

<div class="form">
                 <form method="FAMILY" id="" action="" target="" title="" onsubmit="">
<fieldset>
<legend></legend>
<label for="id"></label>
<select name="number" onchange="window.open(this.value)">                
              <option class="family" value="/ko/">FAMILY SITE</option>
              <option value="http://www.uangelvoice.com/">Uangel Voice</option>
              
              
</select>



</fieldset>
</form>
                </div>
                </div>      
		</div><!--/#sub-footer-wrap-->		
    

</div><!--/#wrap -->

<?php
#-----------------------------------------------------------------
# Special JavaScripts - Do not edit anything below to keep theme functions
#-----------------------------------------------------------------
			
// Google Analytics
if (get_option_tree('google_analytics')) {
	echo stripslashes(get_option_tree('google_analytics'));
}

// Contact Form
if(is_page_template('dynamic-contact-form.php')) {
	callValidator();
}


flexslider();

?>

<?php wp_footer();?>

</body>
</html>

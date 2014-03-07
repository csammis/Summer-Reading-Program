 <!-- footer -->
 <div id="footer">
    <?php SRP_PrintFooterImg(); ?>
    <!-- page block -->
    <div class="block-content">
        
        <div class="copyright">
        <?php echo get_srptheme_message('srp_footertext'); ?>
        </div>
    </div>
    <!-- /page block -->
 </div>
 <!-- /footer -->

<div id="styleswap">
        <script type="text/javascript">
function doStyleSwap()
{
    var isMobile = <?php if (SRP_IsMobile()) echo '1'; else echo '2'; ?>;

    if (isMobile == 1)
    {
        document.cookie = "SRPMobile=2;path=/";
    }
    else
    {
        document.cookie = "SRPMobile=1;path=/";
    }
    location.reload(true);
}
        </script>
        <a href="#" onclick="doStyleSwap(); return false;">
        <?php if (SRP_IsMobile()) echo 'Full Site'; else echo 'Mobile Site'; ?>
        </a>
</div>
<!-- /page -->

  <script type="text/javascript">
  /* <![CDATA[ */
    var isIE6 = false; /* <- do not change! */
    var isIE = false; /* <- do not change! */
    var lightbox = 0;
  /* ]]> */
  </script>
  <!--[if lte IE 6]> <script type="text/javascript"> isIE6 = true; isIE = true; </script> <![endif]-->
  <!--[if gte IE 7]> <script type="text/javascript"> isIE = true; </script> <![endif]-->


<?php wp_footer(); ?>
</body>
</html>


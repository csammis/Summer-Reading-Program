 <!-- footer -->
 <div id="footer">
    <?php SRP_PrintFooterImg(); ?>
    <!-- page block -->
    <div class="block-content">
        <div class="copyright">
        <?php global $SrpMessage; echo $SrpMessage->getFooterText(); ?>
        </div>
    </div>
    <!-- /page block -->
 </div>
 <!-- /footer -->

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


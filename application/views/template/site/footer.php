 <!-- Footer -->
<section class="footer" id="footer_sec">
    <a href="#" class="back-to-top show" title="Move to top"><i class="glyphicon glyphicon-menu-up"></i></a>
    <div class="container">
        <?php if(strip_tags($this->config->item('site_settings')->bottom_section) == "On") { ?>
        <div class="row footer-help-bar">
            <div class="col-md-12">
                <?php if($this->ion_auth->is_tutor() && !is_inst_tutor($this->ion_auth->get_user_id())) { ?>
                <a class="footer-help"><?php echo get_languageword('Need help finding a student');?></a>
                <a href="<?php echo URL_HOME_SEARCH_STUDENT_LEADS; ?>" class="btn btn-footer"> <i class="fa fa-pencil"></i> <?php echo get_languageword('Find Student Leads');?></a>
                <?php } else if($this->ion_auth->is_student()) { ?>
                <a href="<?php echo URL_HOME_SEARCH_TUTOR; ?>" class="footer-help"><?php echo get_languageword('Need help finding a tutor?');?></a>
                <a href="<?php if($this->ion_auth->is_student()) echo URL_STUDENT_POST_REQUIREMENT; else echo URL_AUTH_LOGIN; ?>" class="btn btn-footer"> <i class="fa fa-pencil"></i> <?php echo get_languageword('Post Your Requirement');?></a>
                <?php } else echo '&nbsp;'; ?>

                <?php if(isset($this->config->item('site_settings')->land_line) && $this->config->item('site_settings')->land_line != '') { ?>
                <span class="footer-contact"> <a href="tel:<?php echo $this->config->item('site_settings')->land_line; ?>"><i class="fa fa-phone"></i> <?php echo get_languageword('Feel_free_to_call_us_anytime_on');?>  <strong><?php echo $this->config->item('site_settings')->land_line;?></strong></a></span>
                <?php } ?>
                <hr class="footer-hr-big">
            </div>
        </div>
        <?php } ?>
        <?php if(strip_tags($this->config->item('site_settings')->footer_section) == "On") {

                if(strip_tags($this->config->item('site_settings')->get_app_section) == "Off")
                    $col_size = 12;
                else
                    $col_size = 9;
            ?>
        <div class="row row-margin">
            <?php if(!empty($activemenu) && $activemenu == "home") echo $this->session->flashdata('message'); ?>
            <div class="col-lg-<?php echo $col_size;?> col-md-12 col-sm-12">
                <div class="row">
                    <div class="col-sm-3">
                        <h4 class="footer-head"><?php echo get_languageword('Get to Know Us');?></h4>
                        <ul class="footer-links">
                            <li><a href="<?php echo URL_HOME_ABOUT_US; ?>"><?php echo get_languageword('About Us');?></a></li>
                            <li><a href="<?php echo URL_VIEW_TERMS_AND_CONDITIONS; ?>"><?php echo get_languageword('terms_And_Conditons');?></a></li>
                            <?php if(!$this->ion_auth->logged_in()  || !$this->ion_auth->is_tutor() ){
                                if(!$this->ion_auth->is_institute()){ ?>
                            <li><a href="<?php echo URL_HOME_SEARCH_TUTOR; ?>"><?php echo get_languageword('Search for a Tutor');?></a></li><?php } } ?>
                            <?php if(!$this->ion_auth->logged_in()  || !($this->ion_auth->is_student() || is_inst_tutor($this->ion_auth->get_user_id()))){ ?>
                            <li><a href="<?php echo URL_HOME_SEARCH_STUDENT_LEADS; ?>"><?php echo get_languageword('Search for a Student');?></a></li><?php } ?>
                            <?php if(!$this->ion_auth->logged_in()){ ?>
                            <li><a href="<?php echo URL_AUTH_LOGIN; ?>"><?php echo get_languageword('Become a Tutor');?></a></li><?php } ?>
                            <li><a href="<?php echo URL_HOME_CONTACT_US; ?>"><?php echo get_languageword('Contact Us');?></a></li>

                            <!--blogs link-->
                            <li><a href="<?php echo URL_HOME_LIST_BLOGS; ?>"><?php echo get_languageword('Tutor_Blogs');?></a></li>
                        </ul>
                    </div>

                    <?php
                            $locations = $this->home_model->get_locations(array('child' => true, 'limit' => 7));
                            if(!empty($locations)) {
                    ?>
                    <div class="col-sm-3">
                        <h4 class="footer-head"><?php echo get_languageword('tutors by location');?></h4>
                        <ul class="footer-links">
                            <?php foreach ($locations as $row) { ?>
                            <li title="<?php echo $row->location_name; ?>"><a href="<?php echo URL_HOME_SEARCH_TUTOR.'/by-location/'.$row->slug; ?>"> <?php echo $row->location_name; ?> </a></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php } ?>

                    <?php
                            $courses = $this->home_model->get_courses(array('limit' => 7));
                            if(!empty($courses)) {
                    ?>
                    <div class="col-sm-3">
                        <h4 class="footer-head"><?php echo get_languageword('tutors by course');?></h4>
                        <ul class="footer-links">
                            <?php foreach ($courses as $row) { ?>
                            <li title="<?php echo $row->name; ?>"><a href="<?php echo URL_HOME_SEARCH_TUTOR.'/'.$row->slug;?>"> <?php echo $row->name; ?> </a></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php } ?>

                    <?php
                    if((isset($this->config->item('site_settings')->show_team) && $this->config->item('site_settings')->show_team == 'Yes')) {
                    $team =  $this->base_model->fetch_records_from('team', array('status' => 'Active'), '*', 'name', '', 0, 3);
                    if(!empty($team))
                    {
                    ?>
                    <div class="col-sm-3">
                        <h4 class="footer-head"><?php echo get_languageword('meet the team');?></h4>
                        <?php foreach($team as $t) { ?>
                        <div class="media media-team">
                            <!--<a href="#">-->
                                <figure class="imghvr-zoom-in">
                                    <img class="media-object  img-circle" src="<?php echo URL_PUBLIC_UPLOADS2;?>team/<?php echo $t->image?>" alt="...">
                                    <figcaption></figcaption>
                                </figure>
                                <h4><?php echo $t->name;?></h4>
                                <p><u><?php echo $t->position;?></u></p>
                                <!--</a>-->
                        </div>
                        <?php } ?>
                        </div>
                    <?php }
                    } ?>
                </div>
            </div>
            <?php
            if(strip_tags($this->config->item('site_settings')->get_app_section) == "On") {

            if((isset($this->config->item('site_settings')->androd_app) && $this->config->item('site_settings')->androd_app != '') || (isset($this->config->item('site_settings')->ios_app) && $this->config->item('site_settings')->ios_app != '')) { ?>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <h4 class="footer-color-head"><?php echo get_languageword('Find a tutor fast');?>. <span><?php echo get_languageword('Get our app');?></span>.</h4>
                <p class="footer-text"><?php echo get_languageword('Send a download link to your mail');?>.</p>
                <div class="footer-newsletter">
                    <?php echo form_open('/', 'id="send_app_link_form" class="newsletter-form"'); ?>
                        <div class="input-group "  id="emailtext">
                            <input type="email" class="form-control" value="<?php echo set_value('mailid'); ?>" placeholder="<?php echo get_languageword('your_Email'); ?>" name="mailid" id="mailid" required="required" />
                            <span class="input-group-btn">
                                    <button class="btn newsletter-btn" type="submit"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                                </span>
                        </div>
                        <?php echo form_error('mailid'); ?>
                    <?php echo form_close(); ?>
                </div>

                <div class="footer-appdownload">
                    <hr class="footer-hr hidden-xs">
                    <?php if(isset($this->config->item('site_settings')->ios_app) && $this->config->item('site_settings')->ios_app != '') { ?>
                    <a href="<?php echo $this->config->item('site_settings')->ios_app?>" target="_blank"><img src="<?php echo URL_FRONT_IMAGES;?>icn-istore.png" alt="IOS app link"> <?php echo get_languageword('Apple Store');?></a>
                    <?php } ?>

                    <?php if(isset($this->config->item('site_settings')->androd_app) && $this->config->item('site_settings')->androd_app != '') { ?>
                    <a href="<?php echo $this->config->item('site_settings')->androd_app?>" target="_blank"><img src="<?php echo URL_FRONT_IMAGES;?>icn-playstore.png" alt="Android app link"><?php echo get_languageword('Google Play');?></a>
                    <?php } ?>
                </div>
            </div>
            <?php } } ?>

            <div class="col-lg-3 footer_col">
            <div class="widget-logo"><a href="https://codecanyon.net/item/menorahtutor-tutor-directory-mobile-app/20807493?s_rank=12" target="_blank" title="Get tutors app"><img src="<?php echo URL_FRONT_IMAGES;?>widget-logo.png" alt="Tutors app" /></a></div>
            </div>


        </div>
        <?php } ?>
        <?php if(strip_tags($this->config->item('site_settings')->primary_footer_section) == "On") { ?>
        <div class="row footer-copy-bar">
            <div class="col-md-12">
                <hr class="footer-hr">

                <?php if(isset($this->config->item('site_settings')->designed_by) && $this->config->item('site_settings')->designed_by != '') { ?>
                <span class="copy-right pull-right">
                    <?php
                            echo "<span class='design-by'>".get_languageword('designed_by')."</span> ";
                            if(isset($this->config->item('site_settings')->url_designed_by) && $this->config->item('site_settings')->url_designed_by != '')
                                echo '<a target="_blank" href="'.$this->config->item('site_settings')->url_designed_by.'">'.$this->config->item('site_settings')->designed_by.'</a>';
                            else
                                echo $this->config->item('site_settings')->designed_by;
                    ?> &nbsp; &nbsp;
                    <?php if(isset($this->config->item('site_settings')->rights_reserved_by) && $this->config->item('site_settings')->rights_reserved_by != '') {
                        echo "<span class='design-by'>".$this->config->item('site_settings')->rights_reserved_by."</span>";
                    }
                    ?>


                </span>
                <?php } ?>


                <ul class="social-share">
                    <?php if(isset($this->config->item('social_settings')->facebook) && $this->config->item('social_settings')->facebook != '') { ?>
                    <li class="fb-color"><a href="<?php echo $this->config->item('social_settings')->facebook;?>" target="_blank"><i class="fa fa-facebook"></i></a></li>
                    <?php } ?>
                    <?php if(isset($this->config->item('social_settings')->twitter) && $this->config->item('social_settings')->twitter != '') { ?>
                    <li ><a class="tw-color" href="<?php echo $this->config->item('social_settings')->twitter;?>" target="_blank"><i class="fa fa-twitter"></i></a></li>
                    <?php } ?>
                    <?php if(isset($this->config->item('social_settings')->linkedin) && $this->config->item('social_settings')->linkedin != '') { ?>
                    <li><a class="li-color" href="<?php echo $this->config->item('social_settings')->linkedin;?>" target="_blank"><i class="fa fa-linkedin"></i></a></li>
                    <?php } ?>
                    <?php if(isset($this->config->item('social_settings')->pinterest) && $this->config->item('social_settings')->pinterest != '') { ?>
                    <li class="pi-color"><a href="<?php echo $this->config->item('social_settings')->pinterest;?>" target="_blank"><i class="fa fa-pinterest"></i></a></li>
                    <?php } ?>

                    <?php if(isset($this->config->item('social_settings')->google) && $this->config->item('social_settings')->google != '') { ?>
                    <li class="gp-color"><a href="<?php echo $this->config->item('social_settings')->google;?>" target="_blank"><i class="fa fa-google-plus"></i></a></li>
                    <?php } ?>
                    <?php if(isset($this->config->item('social_settings')->instagram) && $this->config->item('social_settings')->instagram != '') { ?>
                    <li class="ig-color"><a href="<?php echo $this->config->item('social_settings')->instagram;?>" target="_blank"><i class="fa fa-instagram"></i></a></li>
                    <?php } ?>
                    <?php if(isset($this->config->item('social_settings')->youtube) && $this->config->item('social_settings')->youtube != '') { ?>
                    <li class="yt-color"><a href="<?php echo $this->config->item('social_settings')->youtube;?>" target="_blank"><i class="fa fa-youtube-play"></i></a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <?php } ?>
    </div>

</section>
<!-- Ends Footer -->




<!-- Script files -->
<?php
//neatPrint($this->config->item('site_settings'));
if(isset($grocery) && $grocery == TRUE)
{
?>
<!--Image CRUD scripts-->
<?php foreach($js_files as $file): ?>
<script src="<?php echo $file; ?>"></script>
<?php endforeach; ?>
<?php
}
else
{
?>
<script src="<?php echo URL_FRONT_JS;?>jquery.js"></script>

<link rel="stylesheet" href="<?php echo URL_FRONT_CSS;?>jquery-ui.css">
<script src="<?php echo URL_FRONT_JS;?>jquery-ui.js"></script>
<script>
  $( function() {
    $( ".custom_accordion" ).accordion({
        heightStyle: "content"
    });
  });
</script>
<?php
}
?>

<?php if(isset($texteditor) && $texteditor == TRUE) { ?>
<script src="<?php echo base_url(); ?>assets/grocery_crud/texteditor/ckeditor/ckeditor.js"></script>
<script src="<?php echo base_url(); ?>assets/grocery_crud/texteditor/ckeditor/adapters/jquery.js"></script>
<script src="<?php echo base_url(); ?>assets/grocery_crud/js/jquery_plugins/config/jquery.ckeditor.config.js"></script>
<?php } ?>
<?php if(!empty($activemenu) && $activemenu == "sell_courses_online") { ?>
<script> 
//Add/Remove Fields Dynamically - Start
function append_field(max_fields, wrapper_id, add_button_id, appending_div, lbl_txt1, lbl_txt2, field_name1, field_name2)
{

    var wrapper         = $("#"+wrapper_id); //Fields wrapper
    var add_button      = $("#"+add_button_id); //Add button ID
    var cls             = "";
    var attrs           = "";


    var i = ($('#'+wrapper_id+' .'+appending_div).length) + 1; //text box count

    if(i < max_fields) { //max input box allowed
        i++; //text box increment
        $(wrapper).append('<div class="row '+appending_div+'" id="'+appending_div+i+'"><div class="col-sm-5 "><label>'+lbl_txt1+' '+i+'</label><input type="text" name="'+field_name1+'[]" class="form-control" /></div> <div class="col-sm-2 "><label>Source Type</label><select name="source_type[]" id="source_type_'+i+'" class="form-control cls-source_type"><option value="url">URL</option><option value="file">File</option></select></div> <div class="col-sm-4 "><label>'+lbl_txt2+' '+i+'</label><div class="cls-source" id="source_'+i+'"><input type="text" name="'+field_name2+'[]" class="form-control" /></div></div> <div class="col-sm-1"><label>&nbsp;</label><span title="<?php echo get_languageword('remove_this'); ?>" class="btn btn-danger" id="'+i+'" onclick="remove_field(\''+wrapper_id+'\', this.id, \''+appending_div+'\', \''+lbl_txt1+'\', \''+lbl_txt2+'\', \''+field_name1+'\', \''+field_name2+'\');" ><i class="fa fa-minus"></i></span></div></div> '); //add input box
    }

}




function remove_field(wrapper_id, remove_button_id, appending_div, lbl_txt1, lbl_txt2, field_name1, field_name2)
{

    $('#'+appending_div+remove_button_id).remove();

    sort_appended_fields(wrapper_id, appending_div, lbl_txt1, lbl_txt2, field_name1, field_name2);

}





function sort_appended_fields(wrapper_id, appending_div, lbl_txt1, lbl_txt2, field_name1, field_name2)
{

    var field_val       = "";
    var div_field_id    = "";
    var selector        = $('#'+wrapper_id+' .'+appending_div);
    var i               = 1;


    $(selector).each(function() {

        i++;

        div_field_id    = appending_div+i;


        $(this).attr('id', div_field_id);
        $(this).find('label:first').text(lbl_txt1+' '+i);
        $(this).find('label').eq(2).text(lbl_txt2+' '+i);
        $(this).find('span:first').attr('id', i);
        $(this).find('.cls-source_type').attr('id', 'source_type_'+i);
        $(this).find('.cls-source').attr('id', 'source_'+i);

    });

}
//Add/Remove Fields Dynamically - End

$(document).on('change', '.cls-source_type', function() {

    var ref = $(this);
    var sno = ref.attr('id').split('_')[2];
    var refval = ref.val();

    if(refval == "file") {

        $('#source_'+sno).html('<input type="file" name="lesson_file[]" class="form-control" />');

    } else {

        $('#source_'+sno).html('<input type="text" name="lesson_url[]" class="form-control" />');
    }


});



$(document).on('click', '.delete-icon-grocery', function() {

    return confirm("<?php echo get_languageword('Are you sure that you want to delete this record?'); ?>");
});

</script>
<?php } ?>



<!--Bootstrap Page-->
<script src="<?php echo URL_FRONT_JS;?>bootstrap.min.js"></script>
<!--Profile Page-->
<script src="<?php echo URL_FRONT_JS;?>marquee.js"></script>
<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>flatpickr.min.js"></script>

<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>select2.min.js"></script>
<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>owl.carousel.min.js"></script>
<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>jRate.min.js"></script>
<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>jquery.magnific-popup.js"></script>
<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>jquery.smartmenus.js"></script>
<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>jquery.smartmenus.bootstrap.js"></script>
<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>flexgrid.js"></script>
<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>countUp.js"></script>
<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>jquery.dataTables.min.js"></script>
<!-- Custom Script -->
<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>main.js"></script>

<!--Gallery-->
<script type="text/javascript" src="<?php echo URL_FRONT_JS;?>fileinput.min.js"></script>

<?php
if($this->config->item('seo_settings')->google_analytics) {
    echo $this->config->item('seo_settings')->google_analytics;
}
?>

</body>

</html>

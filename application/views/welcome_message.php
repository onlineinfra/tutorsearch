<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Codeigniter page scroll load more </title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?php echo URL_ADMIN_CSS;?>bootstrap.min.css">
    

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    
</head>
<body>

<div class="container" style="margin-top: 120px;">
    <h3>Ajax country list</h3>
    <table class="table">
        <thead>
        <tr><th>SN</th><th>Country name</th><th>Country code</th></tr>
        </thead>

        <tbody id="ajax_table">
        </tbody>
    </table>
    <div class="container" style="text-align: center">
	<button class="btn" id="load_more" data-val = "0">Load more..
	<img style="display: none" id="loader" src="<?php echo str_replace('index.php','',base_url()) ?>asset/loader.GIF"> 
	</button></div>
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="<?php echo URL_ADMIN_JS;?>jquery.js"></script>

<script>
    $(document).ready(function(){
        getcountry(0);

        $("#load_more").on('click', function(e){
            e.preventDefault();
            var page = $(this).data('val');
            getcountry(page);

        });
        //getcountry();
    });

    var getcountry = function(page){
		//alert(page);
        $("#loader").show();
        $.ajax({
            url:"<?php echo base_url() ?>welcome/getCountry",
            type:'GET',
            data: {page:page}
        }).done(function(response){
            $("#ajax_table").append(response);
            $("#loader").hide();
            $('#load_more').data('val', ($('#load_more').data('val')+1));
            scroll();
        });
    };

    var scroll  = function(){
        $('html, body').animate({
            scrollTop: $('#load_more').offset().top
        }, 1000);
    };


</script>
</body>
</html>
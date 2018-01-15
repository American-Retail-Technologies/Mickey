<?php 

header('X-Frame-Options: SAMEORIGIN');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Facebook Like Button</title>
</head>

<body>
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId            : '288040424998871',
      autoLogAppEvents : true,
      xfbml            : true,
      version          : 'v2.11'
    });
    FB.Event.subscribe('edge.create', liked);
    FB.Event.subscribe('edge.remove', notLiked);
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "https://connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
   
  $( document ).ready(function() {
     
  });

</script>

<div class="fb-like" data-href="https://www.facebook.com/americanretailsupply" data-layout="button_count" data-action="like" data-size="large" data-show-faces="true" data-share="false"></div>

<script>
  var liked = function(){
    window.parent.postMessage('Facebook Liked', '*');
  }

  var notLiked = function(){
    window.parent.postMessage('Unliked Facebook Page', '*');
  }
</script>

</body>
</html>

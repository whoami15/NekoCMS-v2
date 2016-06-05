<?php
/*
*  NEKO SIMPLE CMS v1.0.3
* @ Developer: Novi
* @ Email: novhz0514@gmail.com
* @ Github: github.com/novhex
* @ Copyright (c) 2015-2016
* @ License MIT
*/
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="container" style="margin-top: 70px;">
<div class="row">
	<div class="col-md-12">
	
	
	<?php foreach($categ_post as $index): ?>
		<h1 id="article_title" style="text-align: center; margin-top: 50px; font-size: 20px;" ><?php echo $index['title']; ?></h1>
		<hr>

		<strong><?php echo "<i class='fa fa-user'></i>  Posted by: ".$this->pageslib->getAuthorFullName($index['posted_by']); ?></strong>
		<br>
		<strong><?php echo "<i class='fa fa-calendar'></i> Posted on: ".date('F j,  Y',strtotime($index['date_posted']));?></strong>

		<div class="social_media_btns" style="text-align: center;">
			<p style="text-align: center;"> SHARE POST : </p>
			<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo base_url('article').'/'.$index['slug'];?>"><i class="fa fa-facebook-square fa-2x" style="color:#3b5998;"></i></a>
			<a href="https://twitter.com/home?status=<?php echo base_url('article').'/'.$index['slug'];?>"><i class="fa fa-twitter fa-2x" style="color:#337ab7;"></i></a>
			<a href="https://plus.google.com/share?url=<?php echo base_url('article').'/'.$index['slug'];?>"><i class="fa fa-google-plus-square fa-2x" style="color:red;"></i></a>
		</div>

<div id="article_full" style="margin-top: 25px;">
	<?php
     echo $index['content'];
      ?>

</div>
	<?php endforeach; ?>
	</div>
		<div style="text-align: center;"><?php echo $this->pagination->create_links(); ?></div>
	</div>

</div>



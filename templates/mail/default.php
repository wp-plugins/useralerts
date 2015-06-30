<?php

		$metas = get_post_meta($alert->ID);
	    $headers = array('Content-Type: text/html; charset=UTF-8');
		$headers[] = 'From: '.of_get_option( 'useralerts_from_name', get_option('blogname') ).' <'.of_get_option( 'useralerts_from_email', get_option('admin_email') ).'>';
		$to = $metas["email"][0];
		$subject = $alert->post_title.' -- New Matches';

		$s = count($new_posts) > 1 ? 's' : '';
/*
		$body = '<p>'.count($new_posts).' result'.$s.' matched your alert:</p>';
		$body .= '<ul>';
		foreach($new_posts as $c => $post) {
			$content = $post->post_title != '' ? $post->post_title : substr($post->post_content, 0, 20).'...';
			$body .= '<li><a href="'.get_permalink($post->ID).'">'.$content.'</a></li>';
		}
		$body .= '</ul>';
*/


		$body = '<html style="background:#fafafa;">
	<head>
		
		<title>Website Name: '.$alert->post_title.'</title>	
	</head>
	<body>
		<div id="email_container">

		
		<style>
		h2 {
		font-family:helvetica, arial, sans-serif;
		font-size:22px;
		}
		
		h2 {
		margin-bottom:-10px;
		padding-bottom:0;
		}
		</style>
		
			<div style="max-width:550px;  padding:0 20px 20px 20px; background:#fff; margin:30px auto; border:1px #ccc solid;
				 color:#666;line-height:1.5em; " id="email_content">
				
<h1 style="padding:5px 0 0 0; font-family:georgia;font-weight:500;font-size:24px;color:#000;margin-bottom:0;">'.get_option('blogname').'</h1>


					

					
			<h2 style="padding:0; font-family:georgia;font-weight:500;font-size:14px;color:#000;border-bottom:1px solid #bbb; margin-top:5px; margin-bottom:20px; padding-bottom:10px;">"'.$alert->post_title.'"</h2>';

			$body .= '<p>'.count($new_posts).' new posting'.$s.' matching your query</p>
				<br />';
				
				


		foreach($new_posts as $c => $post) {
			$content = $post->post_title != '' ? $post->post_title : substr($post->post_content, 0, 20).'...';
			$body .= '<h2><a href="'.get_permalink($post->ID).'">'.$content.'</a></h2>';
			$body .= '<p>'.substr($post->post_content, 0, 182).'...</p>';
		}
		
		$body .= '<br /><br /><div style="text-align:center; border-top:1px solid #eee;padding:5px 0 0 0;" id="email_footer"> 
					<small style="font-size:11px; color:#999; line-height:14px;">
						You have received this email because this address was used to create an alert subscription via '.get_option("blogname").'.
						If you would like to cancel this subscription, feel free to ';
		var_dump($alert);
		$body .=  $this->generate_unsubscribe_link($alert);
		
		$body .= '</small>
				</div>
				
			</div>
		</div>
	</body>
</html>';
<?php
	echo "<script>
		localStorage.setItem('base_href', '".$_POST['base_href']."');
		window.close();
	</script>";
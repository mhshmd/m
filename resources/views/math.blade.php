<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="robots" content="noindex, nofollow">
	<title>Creating mathematical formulas</title>
	<script src="http://cdn.ckeditor.com/4.6.0/standard-all/ckeditor.js"></script>
</head>

<body>

	<textarea cols="10" id="editor1" name="editor1" rows="10" >
	</textarea>

	<script>
		CKEDITOR.replace( 'editor1', {
			extraPlugins: 'mathjax',
			mathJaxLib: 'http://cdn.mathjax.org/mathjax/2.6-latest/MathJax.js?config=TeX-AMS_HTML',
			height: 320
		} );

		if ( CKEDITOR.env.ie && CKEDITOR.env.version == 8 ) {
			document.getElementById( 'ie8-warning' ).className = 'tip alert';
		}
	</script>
</body>

</html>
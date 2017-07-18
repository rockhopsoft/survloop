<!-- resources/views/vendor/survloop/email.blade.php -->
<style>
body, p, div, table tr td, table tr th, input, textarea, select {
    font-family: {!! $css["font-main"] !!};
    font-style: normal;
    font-weight: 200;
}
body {
    background: {!! $css["color-main-bg"] !!};
}

body, p {
    color: {!! $css["color-main-text"] !!};
}
body, p, div, input, select, textarea, .h3, .h4, .h5, .h6, h1, h2, h3, h4, h5, h6 {
    line-height: 1.42857143;
}
</style>
<html>
<head></head>
<body>
<h1>{{$title}}</h1>
<p>{{$content}}</p>
</body>
</html>
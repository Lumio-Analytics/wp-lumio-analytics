<style>#wpcontent {padding-left: 0px !important;}</style>
<iframe id="wla_frame" src="https://mod.lumio.page/dasb.html"  style="width:100%;height: auto" frameborder="no"></iframe>
<script type="text/javascript">
    jQuery(function(){
        element = document.getElementById('wla_frame');
        yPosition = (element.offsetTop - element.scrollTop + element.clientTop);

        var body = document.body,
            html = document.documentElement;

        var height = Math.max( body.scrollHeight, body.offsetHeight,
            html.clientHeight, html.scrollHeight, html.offsetHeight ) - yPosition;
        document.getElementById('wla_frame').style.height = height+'px';
    });
</script>
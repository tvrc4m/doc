<script id="hidden_template" type="text/html">
    {{each hidden as value name}}
        <input type="hidden" name="{{name}}" value="{{value}}">
    {{/each}}
</script>
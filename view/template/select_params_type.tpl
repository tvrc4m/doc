<select name="return[{{item['name']}}][type]">
    <option value="">--请选择类型--</option>
    {{each params_type as type val}}
		<option {{if type=='string'}}selected{{/if}} value="{{val}}">{{type}}</option>	
    {{/each}}
</select>
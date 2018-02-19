<div class="column col-xs-12 col-sm-6 col-md-4">
    <select name="card_expiration_month" id="card_expiration_month">
        <option value='01'>01</option>
        <option value='02'>02</option>
        <option value='03'>03</option>
        <option value='04'>04</option>
        <option value='05'>05</option>
        <option value='06'>06</option>
        <option value='07'>07</option>
        <option value='08'>08</option>
        <option value='09'>09</option>
        <option value='10'>10</option>
        <option value='11'>11</option>
        <option value='12'>12</option>
    </select>
     /
    <select name="card_expiration_year" id="card_expiration_year">
        {foreach from=$expiration_years item=expiration_year}
            <option value='{$expiration_year}'>{$expiration_year}</option>
        {/foreach}
    </select>
</div>
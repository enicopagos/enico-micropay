<p>
    <label>
        <input type="checkbox" id="eniActivePayment" name="enico_activate_payment" value="on"<?php checked( $enico_activate_payment == "on" ); ?>>Activar Pago
    </label>
</p>
<div id="eniPostOptions">
    <p>
        <label>
            <input type="checkbox" id="eniMinPrice" name="enico_min_price" value="on"<?php checked( $enico_min_price == "on" ); ?>>Precio minimo
        </label>
    </p>
    <p>
        <label>
            Precio: <input type="number" step="0.01" id="eniCustomPrice" name="enico_custom_price" value="<?php echo $enico_custom_price; ?>" placeholder="$<?php echo get_option('enico_default_price'); ?>" style="width:150px;">
            <p>Si el precio no se especifica el valor será el por defecto</p>
        </label>
    </p>
</div>
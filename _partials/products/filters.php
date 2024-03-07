<form action="products.php" method="post" id="filterForm">
    <tr id=filter-row>
        <td>
            <!-- Referencia -->
            <label>
                <input type="text" name="referencia" value="<?= $referencia ?>" size="5">
            </label>
        </td>
        <td>
            <!-- Nombre -->
            <label>
                <input type="text" name="producto" value="<?= $producto ?>">
            </label>
        </td>
        <td><!-- pvp --></td>
        <td><!-- ean --></td>
        <td><!-- estado -->
            <select name="estado">
                <!-- El primer valor es vacío(default) -->
                <option value="" <?= $estado == '' ? 'selected' : '' ?>></option>
                <option value="1" <?= $estado == '1' ? 'selected' : '' ?>>Activo</option>
                <option value="0" <?= $estado == '0' ? 'selected' : '' ?>>Inactivo</option>
        </td>
        <td> <!-- Acciones -->
            <button type="submit" id="submitFilter"><i class="fa-solid fa-filter"></i>Filtrar</button>
            <button type="submit" id="submitReset" onclick="limpiarFiltros()"><i class="fa-solid fa-eraser"></i>Reinicializar</button>
        </td>
    </tr>
</form>
<script>
    function limpiarFiltros() {
        console.log("submitReset");
        // Obtener el formulario
        const formulario = document.getElementById("filterForm");

        // Establecer todos los campos en vacío antes de enviar
        formulario.referencia.value = "";
        formulario.producto.value = "";
        formulario.estado.value = "";

        // Realizar el submit con campos vacíos
        formulario.submit();
    }
</script>

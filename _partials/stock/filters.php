<form action="stock.php" method="post" id="filterForm">
    <tr id=filter-row>
        <td>
            <!-- Formulario de filtrado por ID's de producto -->
            <label>
                <input type="text" name="id_desde" value="<?= $id_desde ?>" placeholder="Desde">
            </label>
            <label>
                <input type="text" name="id_hasta" value="<?= $id_hasta ?>" placeholder="Hasta">
            </label>
        </td>
        <td><!-- Referencia --></td>
        <td><!-- Producto --></td>
        <td><!-- Cantidad --></td>
        <td>
            <!-- Formulario de filtrado por ubicación -->
            <label>
                <input type="text" name="ubicacion" value="<?= $ubicacion ?>" size="10">
            </label>
        </td>
        <td>
            <!-- Acciones -->
            <button type="submit" id="submitFilter"><i class="fa-solid fa-filter"></i>Filtrar</button>
            <button type="submit" id="submitReset" onclick="limpiarFiltros()"><i class="fa-solid fa-eraser"></i>Reinicializar</button>
        </td>
    </tr>
</form>
<script>
    function limpiarFiltros() {
        // Obtener el formulario
        var formulario = document.getElementById("filterForm");

        // Establecer todos los campos en vacío antes de enviar
        formulario.id_desde.value = "";
        formulario.id_hasta.value = "";
        formulario.ubicacion.value = "";

        // Realizar el submit con campos vacíos
        formulario.submit();
    }
</script>

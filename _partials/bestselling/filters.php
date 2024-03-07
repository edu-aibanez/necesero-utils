<form action="bestselling.php" method="post" id="filterForm">
    <tr id=filter-row>
        <td>
            <!-- Formulario de filtrado por fechas -->
            <label>
                <input class="date" type="text" name="fecha_desde" placeholder="Desde" value="<?= $fecha_desde ?>" onfocus="(this.type='date')" onblur="(this.type='text')">
            </label>
            <label>
                <input class="date" type="text" name="fecha_hasta" placeholder="A" value="<?= $fecha_hasta ?>" onfocus="(this.type='date')" onblur="(this.type='text')">
            </label>
        </td>
        <td><!-- Referencia --></td>
        <td><!-- Producto --></td>
        <td><!-- Cantidad vendida --></td>
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
        formulario.fecha_desde.value = "";
        formulario.fecha_hasta.value = "";

        // Realizar el submit con campos vacíos
        formulario.submit();
    }
</script>

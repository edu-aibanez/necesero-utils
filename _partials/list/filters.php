<form action="list.php" method="post" id="filterForm">
    <tr id=filter-row>
        <!-- <td></td> -->
        <td>
            <!-- Formulario de filtrado por fechas -->
            <label>
                <input class="date" type="text" name="fecha_desde" placeholder="Desde" value="<?= $fecha_desde ?>" onfocus="(this.type='date')" onblur="(this.type='text')">
            </label>
            <label>
                <input class="date" type="text" name="fecha_hasta" placeholder="A" value="<?= $fecha_hasta ?>" onfocus="(this.type='date')" onblur="(this.type='text')">
            </label>
        </td>
        <td>
            <!-- Formulario de filtrado por referencia -->
            <label>
                <input type="text" name="referencia" value="<?= $referencia ?>" size="5">
            </label>
        </td>
        <td>
            <!-- Formulario de filtrado por producto -->
            <label>
                <input type="text" name="producto" value="<?= $producto ?>">
            </label>
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>
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
        formulario.fecha_desde.value = "";
        formulario.fecha_hasta.value = "";
        formulario.referencia.value = "";
        formulario.producto.value = "";

        // Realizar el submit con campos vacíos
        formulario.submit();
    }
</script>

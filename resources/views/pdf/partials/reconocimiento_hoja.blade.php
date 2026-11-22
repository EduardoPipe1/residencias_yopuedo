<div class="container {{ !empty($saltoPagina) ? 'page-break' : '' }}">
    <div class="nombre-container">
        <div class="beneficiario-nombre">
            {{ $beneficiario->nombre }} {{ $beneficiario->primer_apellido }} {{ $beneficiario->segundo_apellido }}
        </div>
    </div>

    <div class="fecha-container">
        <div class="lugar-fecha">{{ $beneficiario->grupo?->lugarFechaReconocimiento() }}</div>
    </div>

    <div class="footer-ids">
        {{ $beneficiario->grupo->identificador_grupo ?? '' }} &nbsp;&nbsp;&nbsp;&nbsp; IDSE{{ $beneficiario->folio }}
    </div>
</div>

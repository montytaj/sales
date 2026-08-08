@props([
    'type' => 'card', // 'card', 'table', 'text'
    'rows' => 3
])

@if($type === 'card')
    <div class="card card-custom p-4 placeholder-glow">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="placeholder rounded-circle" style="width: 48px; height: 48px;"></span>
            <div class="w-100">
                <span class="placeholder col-6 rounded"></span>
                <span class="placeholder col-4 rounded d-block mt-1"></span>
            </div>
        </div>
        <span class="placeholder col-12 rounded mb-2"></span>
        <span class="placeholder col-8 rounded"></span>
    </div>
@elseif($type === 'table')
    <div class="table-responsive placeholder-glow">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th><span class="placeholder col-8 rounded"></span></th>
                    <th><span class="placeholder col-6 rounded"></span></th>
                    <th><span class="placeholder col-4 rounded"></span></th>
                    <th><span class="placeholder col-5 rounded"></span></th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < $rows; $i++)
                    <tr>
                        <td><span class="placeholder col-10 rounded"></span></td>
                        <td><span class="placeholder col-8 rounded"></span></td>
                        <td><span class="placeholder col-6 rounded"></span></td>
                        <td><span class="placeholder col-4 rounded"></span></td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
@else
    <div class="placeholder-glow">
        @for($i = 0; $i < $rows; $i++)
            <span class="placeholder col-{{ 12 - ($i % 3) * 2 }} rounded mb-2 d-block"></span>
        @endfor
    </div>
@endif

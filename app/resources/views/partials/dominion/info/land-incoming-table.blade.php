<table class="table">
    <colgroup>
        <col>
        @for ($i = 1; $i <= 12; $i++)
            <col width="20">
        @endfor
        <col width="100">
    </colgroup>
    <thead>
        <tr>
            <th>Land Type</th>
            @for ($i = 1; $i <= 12; $i++)
                <th class="text-center">{{ $i }}</th>
            @endfor
            <th class="text-center">Total</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totals = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
            7 => 0,
            8 => 0,
            9 => 0,
            10 => 0,
            11 => 0,
            12 => 0
            ];
        @endphp
        @foreach ($landHelper->getLandTypes() as $landType)
            <tr>
                <td>
                    {{ ucfirst($landType) }}
                    @if ($landType === $race->home_land_type)
                        <small class="text-muted"><i>(home)</i></small>
                    @endif
                </td>
                @for ($i = 1; $i <= 12; $i++)
                    @php
                        $amount = array_get($data, "incoming.{$landType}.{$i}", 0);
                        $totals[$i] += $amount;
                    @endphp
                    <td class="text-center">
                        @if ($amount === 0)
                            -
                        @else
                            {{ number_format($amount) }}
                        @endif
                    </td>
                @endfor
                <td class="text-center">
                    @if ($amountIncoming = array_get($data, "incoming.{$landType}"))
                        {{ number_format(array_sum($amountIncoming)) }}
                    @else
                        0
                    @endif
                </td>
            </tr>
        @endforeach
        <tr>
            <td>Total</td>
            @foreach($totals as $hourTotal)
                <td class="text-center">
                    @if ($hourTotal === 0)
                        -
                    @else
                        {{ number_format($hourTotal) }}
                    @endif
                </td>
            @endforeach
            <td class="text-center">
                {{ number_format(array_sum($totals)) }}
            </td>
        </tr>
    </tbody>
</table>
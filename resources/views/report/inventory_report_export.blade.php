<table>
    <thead>
        <tr>
            <th colspan="3" style="text-align: right; background-color: #FFFFFF; font-size: 14px; font-weight: bold;">
                Twelveseven Grocery Price List ({{ $categoryName }})
            </th>
        </tr>
        <tr>
            <th colspan="3" style="height: 10px;"></th>
        </tr>
        <tr>
            <th style="background-color: #4472C4; color: #FFFFFF; text-align: center;">S.N</th>
            <th style="background-color: #4472C4; color: #FFFFFF;">Title</th>
            <th style="background-color: #4472C4; color: #FFFFFF;">Unit</th>
            <th style="background-color: #4472C4; color: #FFFFFF;">Price</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
        @foreach($inventoryItems as $item)
        <tr>
            <td style="text-align: center;">{{ $i }}</td>
            <td>{{ $item->title }}</td>
            <td>{{ $item->unit }}</td>
            <td>{{ number_format($item->price_per_unit, 2) }}</td>
        </tr>
        @php $i++; @endphp
        @endforeach
    </tbody>
</table>

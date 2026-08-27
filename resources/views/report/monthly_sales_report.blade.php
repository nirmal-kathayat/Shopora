<table>

    <thead>
        <tr>
            <th style="background-color: #FFA500;">S.N</th>
            <th style="background-color: #FFA500;">Items</th>
            <th style="background-color: #FFA500;">Quantity</th>
            <th style="background-color: #FFA500;">Rate</th>
            <th style="background-color: #FFA500;">Price</th>
            @foreach($monthDays as $days)
            <th style="background-color: yellow;">{{$days}}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php $i =1; @endphp
        @foreach($rowData['preparedData'] as $data)
        <tr>
            <td>{{$i}}</td>
            <td>{{$data['title']}}</td>
            <td>{{$data['qty']}}</td>
            <td>{{$data['rate']}}</td>
            <td>{{$data['qty']* $data['rate']}}</td>
            @foreach($data['insideData'] as $key=>$value)
            <td>{{$value}}</td>
            @endforeach
        </tr>
        @php $i++; @endphp
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td style="background-color: #FFA500; font-weight: bold;">Total</td>
            <td style="background-color: #FFA500; font-weight: bold;">{{$rowData['totalSum'] }}</td>

        </tr>
    </tbody>
</table>
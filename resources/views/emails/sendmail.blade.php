<h1>Pagamento aprovado!</h1> <br>
<hr>
<p>Ordem: {{$order->reference}}</p>
<p>Compra realizada em: {{$order->created_at}}</p>
<hr>
<h2>Sua compra:</h2>
<hr>
@foreach($products as $product)
<p><strong>Login:</strong> {{$product->login}}</p>
<p><strong>Senha:</strong> {{$product->password}}</p>
<p><strong>Nickname:</strong> {{$product->nickname}}</p>
<p><strong>E-mail:</strong> {{$product->email}}</p>
<p><strong>Data de nascimento:</strong> {{$product->birthday}}</p>
<p><strong>Data de criação:</strong> {{$product->created_at}}</p>
<p><strong>Provedor:</strong> {{$product->provider}}</p>
<p><strong>Level:</strong> {{$product->level}}</p>
<p><strong>EA:</strong> {{$product->ea}}</p>
<hr>
@endforeach
<hr>
Faça parte da nossa comunidade e entre no nosso <a href="https://discord.gg/cXsmceww4B">Discord</a>.
Qualquer duvida ou problema, basta abrir um ticket que iremos responder o mais rapido possivel.
<br>
<a href="https://discord.gg/cXsmceww4B">
    <img src="https://i.ibb.co/6D27CZS/discord.png" alt="discord" border="0" height="50">
</a>
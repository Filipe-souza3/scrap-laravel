<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <meta http-equiv="X-UA-Compatible" content="ie=edge"> --}}
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>Pesquisar produto</title>
</head>
<style>
  html,body{margin:0 padding:0}
  header{background-color:green;height:5em;height:10em}
  nav{margin-top:1em;}
  select[name="slc_site"]{margin-left:0.5em}
  button{margin-left:0.5em}
  section{margin-top:1em}
  section>div{width:18rem;margin-left:2.5em;margin-top:1em}
  section>div>div>img{min-height:14;max-height:18em;}
  section>div>div:nth-child(2){min-height:112px}
</style>
<body>
    <header class="container-fluid d-flex align-items-center justify-content-center" style="background-image:url({{asset('storage/bag_shop.jpg')}})  ">
      
      <img src="{{asset('storage/lp.png') ?? ''}}" class="img-fluid" width="120" height="120" >
     
    </header>
    <nav class="container-fluid">
      <form action="{{route('find.product')}}" method="post" class="d-flex justify-content-center" >
        @csrf
        <select class="form-select" name="slc_category" aria-label="Default select example">
          <option value="Outros" selected>Categoria</option>
          <option value="Celular">Celular</option>
          <option value="Geladeira">Geladeira</option>
          <option value="Tv">Tv</option>
        </select>

        <select class="form-select " name="slc_site" aria-label="Default select example" >
          <option value="Outros" selected>Site</option>
          <option value="Mercado livre">Mercado livre</option>
          <option value="Buscape">Buscapé</option>
        </select>
        <input type="text" class="form-control col-lg-3"  id="input" name="input" placeholder="Pesquisar"  style="margin-left:0.5em">
        <button type="submit" class="btn btn-outline-info">Buscar</button>
      </form>
    </nav>

    <section class="row justify-content-center">
      @if(isset($products))
      @foreach($products as $product)

        <div class="card mx-auto d-block">
            <div class="text-center" width="5" height="180">
              <img src="{{$product->path_photo ?? ''}}" class="img-fluid">
            </div>
            <div class="card-body ">
              <p class="card-text align-self-cente">{{$product->description ?? ''}}</p>
            </div>
            <ul class="list-group list-group-flush">
              <li class="list-group-item">Preço: {{$product->price ?? ''}}</li>
              <li class="list-group-item">Site: {{$product->site ?? ''}}</li>
            </ul>
            <ul class="list-group list-group-flush">
              <li class="list-group-item">Categoria: {{$product->category ?? ''}}</li>
            </ul>
        </div>

      @endforeach
      @endif
    </section>
</body>
</html>
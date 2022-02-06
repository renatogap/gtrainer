<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <title>Série</title>
</head>

<body>


  <div id="app" class="container">
    <h3>Série: {{$serie->nome}}</h3>

    <form id="form" method="post" autocomplete="off" @submit.prevent="salvar">
      @csrf

      <input type="hidden" name="id" v-model="id">
      <input type="hidden" name="serie" v-model="serie">

      <div class="mb-3">
        <label for="temporadas" class="form-label">Temporadas</label>
        <input type="text" class="form-control" v-model="temporadas" name="temporadas" required>
      </div>
      <div class="mb-3">
        <label for="episodios" class="form-label">Episódios</label>
        <input type="text" class="form-control" v-model="episodios" name="episodios" required>
      </div>

      <div v-if="message && !error" class="alert alert-success">@{{message}}</div>
      <div v-if="error && !message" class="alert alert-danger">@{{error}}</div>

      <div class="mb-3">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <button v-if="id" @click="resetForm" class="btn btn-danger">Cancelar</button>
      </div>
    </form>

    @if($temporadas)
    <table class="table">
      <tr>
        <th>Temporada</th>
        <th>Episódios</th>
        <th>Ações</th>
      </tr>
      @foreach($temporadas as $temporada)
      <tr>
        <td>{{$temporada->nome}}</td>
        <td>{{$temporada->episodios}}</td>
        <td width="25%">
          <form id="formDelete" method="post" @submit.prevent="deletar(serie.id)">
            @csrf
            <button type="button" @click="acessar(serie.id)" class="btn btn-warning btn-sm">Acessar</button>
            <button type="button" @click="editar(serie.id)" class="btn btn-primary btn-sm">Editar</button>
            <button type="submit" class="btn btn-danger btn-sm pl-2">Excluir</button>
          </form>
        </td>
      </tr>
      @endforeach
    </table>
    @endif
  </div>

  <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
  <script>
    const formDelete = document.getElementById('formDelete');

    const vm = new Vue({
      el: "#app",
      data: {
        id: '',
        temporadas: '',
        episodios: '',
        serie: '{{$serie->id}}',
        message: '',
        error: ''
      },
      methods: {

        salvar() {
          fetch('/temporada', {
              method: 'POST',
              body: new FormData(form)
            })
            .then((response) => response.json())
            .then((response) => {
              this.alertSuccess(response.message);
              this.resetForm();
            })
            .catch((error) => {
              this.alertDanger(error);
            })
        },
        editar(id) {
          fetch('/temporada/edit/' + id)
            .then((response) => response.json())
            .then((response) => {
              this.id = response.id;
              this.temporadas = response.nome;
              this.episodios = response.episodios;
            })
        },
        acessar(id) {
          window.location.href = "/temporada/view/" + id;
        },
        deletar(id) {
          fetch('/temporada/delete/' + id, {
              method: 'POST',
              body: new FormData(formDelete)
            })
            .then((response) => response.json())
            .then((response) => {
              this.alertSuccess(response.message);
              this.resetForm();
            })
        },
        resetForm() {
          this.id = '';
          this.temporadas = '';
          this.episodios = '';
          this.error = '';
        },
        alertSuccess(texto) {
          this.message = texto;

          setTimeout(() => this.message = '', 3000);
        },
        alertDanger(error) {
          this.error = error;
        }
      }
    })
  </script>
  <script>
    const form = document.getElementById('form');
  </script>

</body>

</html>
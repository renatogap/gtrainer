<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <title>Cadastro de Série</title>
</head>

<body>
  <div id="app" class="container">
    <h3>Cadastro de Série</h3>

    <form id="form" action="/serie" method="post" autocomplete="off" @submit.prevent="salvar">
      @csrf

      <input type="hidden" name="id" v-model="id">

      <div class="mb-3">
        <label for="nomeSerie" class="form-label">Nome</label>
        <input type="text" class="form-control" v-model="nomeSerie" name="nomeSerie" required>
      </div>

      <div v-if="message && !error" class="alert alert-success">@{{message}}</div>
      <div v-if="error && !message" class="alert alert-danger">@{{error}}</div>

      <div class="mb-3">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <button v-if="id" @click="resetForm" class="btn btn-danger">Cancelar</button>
      </div>
    </form>

    <table class="table">
      <tr>
        <th>Id</th>
        <th>Séries</th>
        <th>Ações</th>
      </tr>

      <tr v-if="series" v-for="serie in series">
        <td>@{{serie.id}}</td>
        <td width="80%">@{{serie.nome}}</td>
        <td width="25%">
          <form id="formDelete" method="post" @submit.prevent="deletar(serie.id)">
            @csrf
            <button type="button" @click="acessar(serie.id)" class="btn btn-warning btn-sm">Acessar</button>
            <button type="button" @click="editar(serie.id)" class="btn btn-primary btn-sm">Editar</button>
            <button type="submit" class="btn btn-danger btn-sm pl-2">Excluir</button>
          </form>
        </td>
      </tr>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
  <script>
    const formDelete = document.getElementById('formDelete');

    const vm = new Vue({
      el: "#app",
      data: {
        id: '',
        nomeSerie: '',
        series: [],
        message: '',
        error: ''
      },
      created() {
        this.listarSeries();
      },
      methods: {
        listarSeries() {
          fetch("<?= url('series') ?>")
            .then((response) => response.json())
            .then((response) => {
              this.series = response;
            })
        },
        salvar() {
          fetch("<?= url('serie') ?>", {
              method: 'POST',
              body: new FormData(form)
            })
            .then((response) => response.json())
            .then((response) => {
              this.alertSuccess(response.message);
              this.listarSeries();
              this.resetForm();
            })
            .catch((error) => {
              this.alertDanger(error);
            })
        },
        editar(id) {
          fetch("<?= url('serie/edit/') ?>" + id)
            .then((response) => response.json())
            .then((response) => {
              this.id = response.id;
              this.nomeSerie = response.nome;
            })
        },
        acessar(id) {
          window.location.href = "/serie/view/" + id;
        },
        deletar(id) {
          fetch('/serie/delete/' + id, {
              method: 'POST',
              body: new FormData(formDelete)
            })
            .then((response) => response.json())
            .then((response) => {
              this.alertSuccess(response.message);
              this.listarSeries();
              this.resetForm();
            })
        },
        resetForm() {
          this.id = '';
          this.nomeSerie = '';
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
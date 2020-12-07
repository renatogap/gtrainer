class TreinosController {

	constructor() {
		this._form 		= document.getElementById('form');
		this._formFrequencia = document.getElementById('formFrequencia');
		this._formCarga = document.getElementById('formCarga');
		this._formPeriodizacao = document.getElementById('formPeriodizacao');

		this._btDelete 	= document.getElementById('bt-deletar');
		this._msgLoad 	= document.getElementById('msg-load');
		this._modalAlunos = document.getElementById('modal-alunos');
		this._modalExercicios = document.getElementById('modal-exercicios');
		this._panelPeriodizacao = document.querySelectorAll('.panel-periodizacao');
		this._btRemovePeriodizacao = document.getElementById('btRemovePeriodizacao');
		
		if(document.getElementById('id_treino')){
		    this._idTreino = document.getElementById('id_treino').value;
		    
		    this.listarPeriodizacao();

    		this._menuFicha = document.getElementById('menu-ficha');
    
    		$('[data-toggle="popover"]').popover({
    			html: true,
    			content: () => {
    		    	return this._menuFicha.innerHTML;
    		  	}
    		});
		}
	}


	selecionaAluno(id, desc) {
		this._form.aluno.innerHTML = `<option value="${id}">${desc}</option>`;
		this._modalAlunos.querySelector('.close').click();
	}

	selecionaExercicio(id, desc) {
		this._form.exercicio.innerHTML = `<option value="${id}">${desc}</option>`;
		this._modalExercicios.querySelector('.close').click();
	}

	salvar(e) {
		e.preventDefault();

		this._msgLoad.classList.remove('hidden');

		let formData = new FormData(this._form);
		var request = new XMLHttpRequest();
		request.open("POST", this._form.action);

		request.addEventListener("load", function(resp){
			let json = JSON.parse(resp.target.response);

			alert(json.msg);
			if(json.retorno === 'sucesso'){
				window.location = BASE_URL+'admin/treino-aluno/id/'+json.id;
			}
		});

		request.addEventListener("error", function(resp){
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send(formData);
	}

	alterar(e) {
		e.preventDefault();

		this._msgLoad.classList.remove('hidden');

		let formData = new FormData(this._form);
		var request = new XMLHttpRequest();
		request.open("POST", this._form.action);

		request.addEventListener("load", function(resp){
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
			if(json.retorno === 'sucesso'){
				window.location.reload();
			}
		});

		request.addEventListener("error", function(resp){
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send(formData);
	}

	showDivPeriodizacao(resp) {
		let size = this._panelPeriodizacao.length;
		if(size > 0){
			for(let i=0; i < size; i++){
				this._panelPeriodizacao[i].innerHTML = resp;
			}
		}
	}

	listarPeriodizacao(){
		var request = new XMLHttpRequest();
		request.open("POST", `${BASE_URL}admin/lista-periodizacao/id_treino/${this._idTreino}`);

		request.addEventListener("load", (resp) => {
			this.showDivPeriodizacao(resp.target.response);

			document.getElementById('div-periodizacao').querySelectorAll('table tr td.edit').forEach(td => td.remove());
			document.getElementById('div-periodizacao').querySelectorAll('table tr th.edit').forEach(td => td.remove());
		});

		request.addEventListener("error", function(resp){
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send();
	}

	salvarPeriodizacao(e) {
		e.preventDefault();

		this._msgLoad.classList.remove('hidden');

		let formData = new FormData(this._formPeriodizacao);
		var request = new XMLHttpRequest();
		request.open("POST", this._formPeriodizacao.action);

		request.addEventListener("load", (resp) => {
			let json = JSON.parse(resp.target.response);

			//alert(json.msg);
			if(json.retorno === 'sucesso'){
				this._msgLoad.classList.add('hidden');
				this.listarPeriodizacao();
				this.limparFormPeriodizacao();
			}
		});

		request.addEventListener("error", function(resp){
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send(formData);
	}

	limparFormPeriodizacao(){
		this._formPeriodizacao.id.value = '';
		this._formPeriodizacao.secao.value = '';
		this._formPeriodizacao.descricao.value = '';
		this._formPeriodizacao.dias.value = '';
		this._btRemovePeriodizacao.classList.add('hidden');
	}

	editaPeriodizacao(e) {
		let json;

		var request = new XMLHttpRequest();
		request.open("GET", e.dataset.url);

		request.addEventListener("load", (resp) => {
			json = JSON.parse(resp.target.response);
			this._formPeriodizacao.id.value 	   = json.id;
			this._formPeriodizacao.secao.value 	   = json.secao;
			this._formPeriodizacao.dias.value 	   = json.dias;
			this._formPeriodizacao.descricao.value = json.descricao;

			this._btRemovePeriodizacao.classList.remove('hidden');
		});

		request.addEventListener("error", function(resp){
			json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send();
	}

	removePeriodizacao(e) {
		var conf = confirm('Deseja realmente remover esta periodização?')

		if(conf) {
			let json;
			this._msgLoad.classList.remove('hidden');

			var request = new XMLHttpRequest();
			request.open("GET", `${e.dataset.url}/${this._formPeriodizacao.id.value}`);

			request.addEventListener("load", (resp) => {
				json = JSON.parse(resp.target.response);

				if(json.retorno === 'sucesso'){
					this._msgLoad.classList.add('hidden');
					this.listarPeriodizacao();
					this.limparFormPeriodizacao();
				}
			});

			request.addEventListener("error", function(resp){
				json = JSON.parse(resp.target.response);
				alert(json.msg);
			});

			request.send();
		}
	}

	salvarExercicio(e, aluno=false) {
		e.preventDefault();

		this._msgLoad.classList.remove('hidden');

		let formData = new FormData(this._form);
		var request = new XMLHttpRequest();
		request.open("POST", this._form.action);

		request.addEventListener("load", (resp) => {
			let json = JSON.parse(resp.target.response);

			if(json.retorno === 'erro'){
				alert(json.msg);
			}
			else if(!aluno && confirm(json.msg+'. Deseja adicionar outro exercício?')) {
				window.location.reload();
			}else {
				window.location = BASE_URL+'admin/treino-aluno/id/'+this._form.idTreino.value;
			}
		});

		request.addEventListener("error", function(resp){
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send(formData);
	}

	salvarCarga(e) {
		e.preventDefault();

		this._msgLoad.classList.remove('hidden');

		let formData = new FormData(this._formCarga);
		var request = new XMLHttpRequest();
		request.open("POST", this._formCarga.action);

		request.addEventListener("load", (resp) => {
			let json = JSON.parse(resp.target.response);

			alert(json.msg);
			if(json.retorno === 'sucesso'){
				window.location.reload();
			}

		});

		request.addEventListener("error", function(resp){
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send(formData);
	}

	salvarFrequencia(e) {
		e.preventDefault();

		this._msgLoad.classList.remove('hidden');

		let formData = new FormData(this._formFrequencia);
		var request = new XMLHttpRequest();
		request.open("POST", this._formFrequencia.action);

		request.addEventListener("load", (resp) => {
			let json = JSON.parse(resp.target.response);

			alert(json.msg);
			if(json.retorno === 'sucesso'){
				document.getElementById('dtFrequencia').value = '';
				window.location.reload();
			}

		});

		request.addEventListener("error", function(resp){
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send(formData);
	}


	deletarExercicio(e, id) {
		var conf = confirm('Deseja realmente remover este exercício?')

		if(conf) {
			let json;
			this._msgLoad.classList.remove('hidden');

			var request = new XMLHttpRequest();
			request.open("GET", e.dataset.url);

			request.addEventListener("load", (resp) => {
				json = JSON.parse(resp.target.response);

				alert(json.msg);
				if(json.retorno === 'sucesso'){
					window.location = BASE_URL+'admin/treino-aluno/id/'+id;
				}
			});

			request.addEventListener("error", function(resp){
				json = JSON.parse(resp.target.response);
				alert(json.msg);
			});

			request.send();
		}
	}

}
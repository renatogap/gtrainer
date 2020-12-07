class AlunosController {

	constructor() {
		this._form 		= document.getElementById('form');
		this._fotoAluno	= document.getElementById('foto-aluno');
		this._btDelete 	= document.getElementById('bt-deletar');
		this._msgLoad 	= document.getElementById('msg-load');
	}

	mostrarSenha(e) {
		this._form.senhaCad.type = (e.checked? 'text' : 'password');
	}

	salvar(e) {
		e.preventDefault();

		this._msgLoad.classList.remove('hidden');

		let formData = new FormData(this._form);
		var request = new XMLHttpRequest();
		request.open("POST", this._form.action);

		request.addEventListener("load", (resp) => {
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
			this._msgLoad.classList.add('hidden');
			if(json.retorno === 'sucesso') {
				window.location = BASE_URL+'admin/aluno';
			}
		});

		request.addEventListener("error", (resp) => {
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send(formData);
	}

	deletar(e) {
		var conf = confirm('Deseja realmente deletar este aluno?');

		if(conf) {
			let json;
			this._msgLoad.classList.remove('hidden');

			var request = new XMLHttpRequest();
			request.open("POST", e.dataset.url);

			request.addEventListener("load", (resp) => {
				json = JSON.parse(resp.target.response);
				alert(json.msg);
				this._msgLoad.classList.add('hidden');
				window.history.back();
				//window.location = BASE_URL+'admin/aluno';
			});

			request.addEventListener("error", (resp) => {
				json = JSON.parse(resp.target.response);
				alert(json.msg);
			});

			request.send();
		}
	}

	verificaUsuarioExistente(e) {
		if(this._form.email.value) {
			let json;
			this._msgLoad.classList.remove('hidden');

			var request = new XMLHttpRequest();
			request.open("GET", `${e.dataset.url}/${this._form.email.value}/id/${this._form.id.value}`);

			request.addEventListener("load", (resp) => {
	            this._msgLoad.classList.add('hidden');
	            
				json = JSON.parse(resp.target.response);
				
				if(json.retorno == 'cancelar'){
	                alert(json.msg);
	                this._form.email.readOnly = true;
	                //this._fotoAluno.innerHTML = "<img src='<?= $this->baseUrl() ?>"+resp.dados.foto+"' height='180' />";
	            }
			});

			request.addEventListener("error", (resp) => {
				json = JSON.parse(resp.target.response);
				alert(json.msg);
				this._msgLoad.classList.add('hidden');
			});

			request.send();
		}
	}

}
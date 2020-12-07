class GrupoMuscularController {

	constructor() {
		this._form 		= document.getElementById('form');
		this._btDelete 	= document.getElementById('bt-deletar');
		this._msgLoad 	= document.getElementById('msg-load');
	}

	editar(e) {
		let formData = new FormData(this._form);
		var request = new XMLHttpRequest();
		request.open("GET", e.dataset.url);

		request.addEventListener("load", (resp) => {
			let json = JSON.parse(resp.target.response);
			this._btDelete.classList.remove('hidden');
			this._form.id.value = json.id;
			//this._form.grupo.value = json.fk_grupo;
			this._form.nome.value = json.nome;
		});

		request.addEventListener("error", (resp) => {
			let json = JSON.parse(resp.target.response);
			alert(json.message);
		});

		request.send(formData);
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
				window.location.reload();
			}
		});

		request.addEventListener("error", (resp) => {
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send(formData);
	}

	deletar(e) {
		var conf = confirm('Deseja realmente deletar este grupo muscular?');

		if(conf) {
			let json;
			this._msgLoad.classList.remove('hidden');

			var request = new XMLHttpRequest();
			request.open("GET", `${e.dataset.url}${this._form.id.value}`);

			request.addEventListener("load", (resp) => {
				json = JSON.parse(resp.target.response);
				alert(json.msg);
				this._msgLoad.classList.add('hidden');
				window.location.reload();
			});

			request.addEventListener("error", (resp) => {
				json = JSON.parse(resp.target.response);
				alert(json.msg);
			});

			request.send();
		}
	}

	inicializarForm() {
		this._form.id.value 	= '';
		//this._form.grupo.value  = '';
		this._form.nome.value 	= '';
	}

}
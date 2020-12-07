class ExerciciosController {

	constructor() {
		this._form 		= document.getElementById('form');
		this._btDelete 	= document.getElementById('bt-deletar');
		this._msgLoad 	= document.getElementById('msg-load');
		this._divGrupoMuscular = document.getElementById('div_grupo_muscular');
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
			if(json.retorno == 'sucesso') {
				window.location = BASE_URL+'admin/exercicio';
			}
		});

		request.addEventListener("error", function(resp){
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send(formData);
	}

	deletar(e) {
		var conf = confirm('Deseja realmente remover este exercício?')

		if(conf) {
			let json;
			this._msgLoad.classList.remove('hidden');

			var request = new XMLHttpRequest();
			request.open("GET", e.dataset.url);

			request.send();

			request.addEventListener("load", function(resp){
				json = JSON.parse(resp.target.response);
				alert(json.msg);
				if(json.retorno === 'sucesso'){
					window.location = BASE_URL+'admin/exercicio'
				}
			});

			request.addEventListener("error", function(resp){
				json = JSON.parse(resp.target.response);
				alert(json.msg);
			});

		}
	}

	showGrupoMuscular(value) {
		if(value == 1) {
			this._divGrupoMuscular.classList.remove('hidden')
			this._form.grupo_muscular.required = true;
		}else {
			this._divGrupoMuscular.classList.add('hidden')
			this._form.grupo_muscular.required = false;
		}
	}

}
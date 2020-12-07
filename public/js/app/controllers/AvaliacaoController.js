class AvaliacaoController {

	constructor() {
		this._form 		  = document.getElementById('form');
		this._msgLoad 	  = document.getElementById('msg-load');
		this._modalAlunos = document.getElementById('modal-alunos');
	}

	selecionaAluno(id, desc) {
		this._form.aluno.innerHTML = `<option value="${id}">${desc}</option>`;
		this._modalAlunos.querySelector('.close').click();
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
				window.location = BASE_URL+'admin/avaliacao-fisica-view/id/'+json.id;
			}
		});

		request.addEventListener("error", function(resp){
			let json = JSON.parse(resp.target.response);
			alert(json.msg);
		});

		request.send(formData);
	}
}
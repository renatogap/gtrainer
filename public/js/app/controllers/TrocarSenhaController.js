class TrocarSenhaController {

	constructor() {
		this._form 		= document.getElementById('form-trocar-senha');
		this._msgLoad 	= document.getElementById('aguarde');
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
				this._form.querySelector('#btnCancelar').click();
			}
		});

		request.addEventListener("error", (resp) => {
			let json = JSON.parse(resp.target.response);
			alert(json.message);
		});

		request.send(formData);
	}

}

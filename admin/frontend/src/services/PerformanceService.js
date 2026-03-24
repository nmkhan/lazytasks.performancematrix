class PerformanceService {
	getHeaders() {
		const headers = {
			'Content-Type': 'application/json',
			'X-WP-Nonce': window.appLocalizerPerformance?.nonce || window.appLocalizer?.nonce || ''
		};

		try {
			const rawPersistData = localStorage.getItem('admin');
			if (rawPersistData) {
				const persistData = JSON.parse(rawPersistData);
				if (persistData.auth) {
					const authData = JSON.parse(persistData.auth);
					if (authData?.session?.token) {
						headers['Authorization'] = `Bearer ${authData.session.token}`;
					}
				}
			}
		} catch (e) {
			console.error('Failed to parse host auth token:', e);
		}

		return headers;
	}

	getBaseUrl() {
		const apiRoot = window.appLocalizerPerformance?.apiUrl || window.appLocalizer?.apiUrl || '';
		return `${apiRoot}/lazytasks/api/v3/performance`;
	}

	async getRules() {
		const response = await fetch(`${this.getBaseUrl()}/rules`, {
			method: 'GET',
			headers: this.getHeaders(),
		});
		if (!response.ok) throw new Error('API Request Failed');
		return await response.json();
	}

	async updateRules(payload) {
		const response = await fetch(`${this.getBaseUrl()}/rules`, {
			method: 'POST',
			headers: this.getHeaders(),
			body: JSON.stringify(payload),
		});
		if (!response.ok) throw new Error('API Request Failed');
		return await response.json();
	}

	async getProjectScores(projectId, timeframe = 'all_time') {
		const response = await fetch(`${this.getBaseUrl()}/scores/${projectId}?timeframe=${encodeURIComponent(timeframe)}`, {
			method: 'GET',
			headers: this.getHeaders(),
		});
		if (!response.ok) throw new Error('API Request Failed');
		return await response.json();
	}

	async syncBacklog(reset = false) {
		const response = await fetch(`${this.getBaseUrl()}/sync`, {
			method: 'POST',
			headers: this.getHeaders(),
			body: JSON.stringify({ reset }),
		});
		if (!response.ok) throw new Error('API Request Failed');
		return await response.json();
	}
}

export default new PerformanceService();

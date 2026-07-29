import axios, { AxiosRequestConfig, AxiosResponse } from 'axios';
import { ref } from 'vue';

const apiInstance = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
});

apiInstance.interceptors.request.use((config) => {
    config.withCredentials = true; // Send Laravel session cookies

    // Laravel XSRF-TOKEN cookie → header (needed for POST/DELETE)
    if (typeof document !== 'undefined') {
        const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
        if (match?.[1]) {
            config.headers = config.headers ?? {};
            config.headers['X-XSRF-TOKEN'] = decodeURIComponent(match[1]);
        }
    }

    return config;
});

export function useApi() {
    const request = async (
        method: 'get' | 'post' | 'put' | 'delete',
        url: string,
        payload: any = {},
        options: AxiosRequestConfig = {},
    ) => {
        const data = ref<any>(null);
        const error = ref<any>(null);

        try {
            const response: AxiosResponse = await apiInstance({
                method,
                url,
                data: payload,
                ...options,
            });
            data.value = response.data;
        } catch (err: any) {
            if (axios.isAxiosError(err)) {
                error.value = err.response?.data || err;
            } else {
                error.value = err;
            }
        }

        return { data: data.value, error: error.value };
    };

    // Helper shortcuts
    const get = (url: string, options: AxiosRequestConfig = {}) =>
        request('get', url, {}, options);
    const post = (
        url: string,
        payload: any = {},
        options: AxiosRequestConfig = {},
    ) => request('post', url, payload, options);
    const put = (
        url: string,
        payload: any = {},
        options: AxiosRequestConfig = {},
    ) => request('put', url, payload, options);
    const del = (url: string, options: AxiosRequestConfig = {}) =>
        request('delete', url, {}, options);

    return { api: request, get, post, put, del };
}

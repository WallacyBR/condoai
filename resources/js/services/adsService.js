import api from '@/utils/api';

export default {
  getAllAds(params) {
    return api.get('/ads', { params });
  },

  getAd(id) {
    return api.get(`/ads/${id}`);
  },

  createAd(formData) {
    return api.post('/ads', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },

  updateAd(id, formData) {
    // Laravel expects PUT/PATCH for updates, but FormData requires POST with _method spoofing
    formData.append('_method', 'PUT'); 
    return api.post(`/ads/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },

  deleteAd(id) {
    return api.delete(`/ads/${id}`);
  },

  markAdAsSold(id) {
    return api.patch(`/ads/${id}/mark-sold`);
  },

  markAdAsUnsold(id) {
    return api.patch(`/ads/${id}/mark-unsold`);
  },

  reportAd(id, reason) {
    return api.post(`/ads/${id}/report`, { reason });
  },

  recordContactClick(id) {
    return api.post(`/ads/${id}/contact-click`);
  },

  getMyAds() {
    return api.get('/my-ads');
  },

  getAdStatistics(id) {
    return api.get(`/ads/${id}/statistics`);
  },
};

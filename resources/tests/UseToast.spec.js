import { useToast } from '../js/composables/useToast';

describe('useToast', () => {
  beforeEach(() => {
    useToast().clearAllToasts();
  });

  it('expoe aliases compatíveis para toasts de sucesso e erro', () => {
    const toast = useToast();

    toast.successToast('Operação concluída');
    toast.errorToast('Operação falhou');

    expect(toast.toasts.value).toEqual(expect.arrayContaining([
      expect.objectContaining({ message: 'Operação concluída', type: 'success' }),
      expect.objectContaining({ message: 'Operação falhou', type: 'error' }),
    ]));
  });

  it('expoe aliases compatíveis para toasts de alerta e informação', () => {
    const toast = useToast();

    toast.warningToast('Atenção');
    toast.infoToast('Informação');

    expect(toast.toasts.value).toEqual(expect.arrayContaining([
      expect.objectContaining({ message: 'Atenção', type: 'warning' }),
      expect.objectContaining({ message: 'Informação', type: 'info' }),
    ]));
  });
});

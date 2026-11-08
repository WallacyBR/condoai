function base64UrlToBuffer(value) {
  const base64 = value.replace(/-/g, '+').replace(/_/g, '/');
  const padded = base64.padEnd(base64.length + ((4 - (base64.length % 4)) % 4), '=');
  const binary = atob(padded);
  const bytes = new Uint8Array(binary.length);

  for (let index = 0; index < binary.length; index += 1) {
    bytes[index] = binary.charCodeAt(index);
  }

  return bytes.buffer;
}

function bufferToBase64Url(buffer) {
  const bytes = new Uint8Array(buffer || new ArrayBuffer(0));
  let binary = '';

  bytes.forEach((byte) => {
    binary += String.fromCharCode(byte);
  });

  return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
}

function prepareCredentialDescriptors(credentials = []) {
  return credentials.map((credential) => ({
    ...credential,
    id: base64UrlToBuffer(credential.id)
  }));
}

function currentHostname() {
  return typeof window !== 'undefined' ? window.location.hostname : '';
}

function currentLocalhostUrl() {
  if (typeof window === 'undefined') {
    return 'http://localhost';
  }

  const { protocol, port, pathname, search, hash } = window.location;
  const suffix = `${port ? `:${port}` : ''}${pathname}${search}${hash}`;

  return `${protocol}//localhost${suffix}`;
}

function isIpLiteral(hostname) {
  return /^(\d{1,3}\.){3}\d{1,3}$/.test(hostname) || hostname.includes(':');
}

export function passkeyOriginProblem() {
  if (typeof window === 'undefined') {
    return '';
  }

  if (!window.isSecureContext) {
    return 'Passkeys exigem HTTPS em produção ou localhost no ambiente local.';
  }

  if (isIpLiteral(currentHostname())) {
    return `Passkeys não funcionam em IP local neste navegador. Acesse ${currentLocalhostUrl()} para usar biometria, PIN ou senha do dispositivo.`;
  }

  return '';
}

export function browserSupportsPasskeys() {
  return typeof window !== 'undefined'
    && window.isSecureContext
    && typeof navigator !== 'undefined'
    && !!navigator.credentials
    && typeof PublicKeyCredential !== 'undefined'
    && passkeyOriginProblem() === '';
}

export async function createPasskeyCredential(publicKey) {
  const originProblem = passkeyOriginProblem();
  if (originProblem) {
    throw new Error(originProblem);
  }

  const options = {
    ...publicKey,
    challenge: base64UrlToBuffer(publicKey.challenge),
    user: {
      ...publicKey.user,
      id: base64UrlToBuffer(publicKey.user.id)
    },
    excludeCredentials: prepareCredentialDescriptors(publicKey.excludeCredentials || [])
  };

  const credential = await navigator.credentials.create({ publicKey: options });

  return {
    id: credential.id,
    rawId: bufferToBase64Url(credential.rawId),
    type: credential.type,
    response: {
      attestationObject: bufferToBase64Url(credential.response.attestationObject),
      clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
      transports: typeof credential.response.getTransports === 'function'
        ? credential.response.getTransports()
        : []
    },
    clientExtensionResults: credential.getClientExtensionResults()
  };
}

export async function getPasskeyCredential(publicKey) {
  const originProblem = passkeyOriginProblem();
  if (originProblem) {
    throw new Error(originProblem);
  }

  const options = {
    ...publicKey,
    challenge: base64UrlToBuffer(publicKey.challenge),
    allowCredentials: prepareCredentialDescriptors(publicKey.allowCredentials || [])
  };

  const credential = await navigator.credentials.get({ publicKey: options });

  return {
    id: credential.id,
    rawId: bufferToBase64Url(credential.rawId),
    type: credential.type,
    response: {
      authenticatorData: bufferToBase64Url(credential.response.authenticatorData),
      clientDataJSON: bufferToBase64Url(credential.response.clientDataJSON),
      signature: bufferToBase64Url(credential.response.signature),
      userHandle: credential.response.userHandle
        ? bufferToBase64Url(credential.response.userHandle)
        : null
    },
    clientExtensionResults: credential.getClientExtensionResults()
  };
}

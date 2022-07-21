import React from 'react';
import { createRoot } from 'react-dom/client';
import CommitmentSlideOver from '../components/Home/CommitmentSlideOver';

export default () => createRoot(dom('#modal-wrapper')).render(<CommitmentSlideOver />);

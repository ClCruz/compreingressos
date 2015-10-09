/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.compreingressos.knowledge.bean;

import com.compreingressos.knowledge.model.Usuario;
import java.util.List;
import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.NoResultException;
import javax.persistence.PersistenceContext;

/**
 *
 * @author edicarlos.barbosa
 */
@Stateless
public class UsuarioFacade extends AbstractFacade<Usuario> {
    @PersistenceContext(unitName = "KnowledgePU")
    private EntityManager em;

    @Override
    protected EntityManager getEntityManager() {
        return em;
    }

    public UsuarioFacade() {
        super(Usuario.class);
    }

    @Override
    public List<Usuario> findAll() {
        return getEntityManager().createNamedQuery("Usuario.findAll").getResultList();
    }
    
    public Usuario login(Usuario usuario) {
        try {
            return (Usuario) getEntityManager().createNamedQuery("Usuario.login")
                    .setParameter("login", usuario.getLogin())
                    .setParameter("password", usuario.getPassword())
                    .getSingleResult();
        } catch (NoResultException ex) {            
            return null;
        }
    }
}
